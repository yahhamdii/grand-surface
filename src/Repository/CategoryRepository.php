<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use App\Entity\Category;
use App\Entity\ClientStatus;
use App\Entity\GroupItem;
use Sogedial\OAuthBundle\Entity\UserAdmin;
use Sogedial\OAuthBundle\Entity\UserCommercial;
use Sogedial\OAuthBundle\Entity\UserCustomer;

class CategoryRepository extends EntityRepository
{
    protected $alias = 'ca';

    public function findBy(array $filter, array $orderBy = null, $limit = null, $offset = null) {

        $qb = $this->createFindByQueryBuilder($filter, $orderBy, $limit, $offset);        

        $qbFinal = $this->getAcl($qb, $filter);

        return $qbFinal->getQuery()->getResult();
    }


    public function getCount(array $filter) {

        $qb = $this->createCountQueryBuilder($filter);

        $qbFinal = $this->getAcl($qb, $filter);

        return $qbFinal->getQuery()->getOneOrNullResult();
    }

    protected function getAcl($qb, $filter = []){

        $user = $this->tokenStorage->getToken()->getUser();
        
        if ($user instanceof UserCommercial || $user instanceof UserAdmin) {
            $platform = $user->getPlatform();
            
            $qb->innerJoin( $this->getAlias().'.items','items')
            ->innerJoin( 'items.platform','platform')
            ->andWhere('platform = :platform')
            ->andWhere('items.active = 1')
            ->setParameter('platform',  $platform);

            return $qb;
        }

        if ($user instanceof UserCustomer) {
            $platform = $filter['platform'];
            $groupItems = $user->getClient()->getAllGroupItems($platform);
            
            foreach($groupItems as $groupItem){                
                $groupItemsIds[] = $groupItem->getId();
            }

            $qb->innerJoin( $this->getAlias().'.items','items')
            ->innerJoin('items.groupItems', 'groupItems')
            ->andWhere('groupItems.id IN (:groupItemsIds)')
            ->andWhere('items.active = 1')
            ->setParameter('groupItemsIds', $groupItemsIds)
            ->groupBy($this->getAlias() . '.id');

            return $qb;            
        }
    }

    public function getUserAllCategories($platform = null, $client=null, $showItems = false, $status = '')
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $user = $this->tokenStorage->getToken()->getUser();
        
        if ($user instanceof UserCustomer || $client !== null) {
            $client = ($user instanceof UserCustomer)?$user->getClient():$client;

            $groupItems = $client->getAllGroupItems($platform, $status);             
            $groupItems = $this->filterByClientStatus($platform, $client, $groupItems);
            
            return $this->getCategoriesByGroupItems($groupItems, $showItems);
        }
    }

    public function getCategoriesByGroupItems($groupItems, $showItems = false, $mergeAll = false)
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        
        foreach($groupItems as $i => $groupItem){
            $groupItemToMerge =  ($mergeAll && $i > 0)?$groupItems[0]:null;
            $this->_setGroupItemCategories($groupItem, $showItems, $groupItemToMerge);
        }

        if( $mergeAll ){            
            return [$groupItems[0]];
        }

        return $groupItems;           
    }

    private function _setGroupItemCategories($groupItem, $showItems = false, $groupItemToMerge = null){
        $categories = ($showItems) ? $this->_getGroupResults($groupItem->getId())
                                 : $this->_getOptimizedGroupResults($groupItem->getId());       
               
        $parentsIds = [];
        $lastChilds = [];

        foreach($categories as $key=>$value){
            $lastChilds[$value['id']] = $this->_getCategoryToArray($value, $showItems, true);
            $parentsIds = array_merge($parentsIds,explode('-', $value['path']));
        }

        
               
        $qb = $this->createQueryBuilder($this->getAlias());
        $qb->andWhere($qb->expr()->in($this->alias . '.id', ':parentsIds'))
            ->setParameter('parentsIds', array_unique($parentsIds));

        $parents = $qb->getQuery()->getArrayResult();
        $qb->getEntityManager()->clear();
        
        $allCategories = [];
        foreach($parents as $key=>$value){    
            $allCategories[$value['id']] = $this->_getCategoryToArray($value, $showItems);
        }

        $allCategories = $this->_setCounts($allCategories, $categories);
        $allCategories = array_merge($allCategories, $lastChilds);
        
        $finalCategories = [];
        foreach($allCategories as $key=>$value){
            if($allCategories[$key]['parentId'] == ""){
                $finalCategories[$key] = $allCategories[$key];
                $finalCategories[$key]['children'] = $this->_setChildren( $finalCategories[$key], $allCategories );
            }           
        }

        if($groupItemToMerge !== null){
            $groupItemToMerge->setCategories($finalCategories);
            return $groupItemToMerge;
        }else{
            $groupItem->setCategories($finalCategories);
            return $groupItem;
        }
    }

    private function _getCategoryToArray($category, $showItems = false, $isLastChild = false){
        $path = $category['path'];
        $p = explode('-',$path);
        $parentId = $p[count($p)-1];

        return [            
            'id'=>$category['id'],
            'name'=>$category['name'],
            'slug'=>$category['slug'],
            'type'=>$category['type'],
            'path'=>$category['path'],
            'parentId'=>$parentId,
            'count_items'=>(isset($category['count_items']))?$category['count_items']:0,
            'items'=>($showItems && $isLastChild && isset($category['items']))?$category['items']:[],            
        ];
    }

    private function _getOptimizedGroupResults($groupId){
        $qb = $this->createQueryBuilder($this->getAlias())        
        ->addSelect('count(distinct(items.id))')
        ->innerJoin( $this->getAlias().'.items','items')
        ->innerJoin('items.groupItems', 'groupItems')
        ->where('groupItems.id = :groupItemsId')        
        ->andWhere('items.active = 1')        
        ->orderBy($this->getAlias().'.name', 'ASC')
        ->groupBy($this->getAlias().'.id');
        /* Fix for optimization */        
        $conn = $this->getEntityManager()->getConnection();
        $sql = $qb->getQuery()->getSQL();        
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(1, $groupId);
        $stmt->execute();
        $categories = [];
        foreach( $stmt->fetchAll() as $row=>$category){
            foreach( $category as $column=>$value){
                $key = substr($column,0, strrpos($column, '_'));
                if($key == 'sclr'){
                    $key = 'count_items';
                }
                $categories[$row][$key] = $value;
            }            
        }
        return $categories;
    }

    private function _getGroupResults($groupId){
        $qb = $this->createQueryBuilder($this->getAlias())
        // Needs it to have the good number of count Items 
        ->addSelect('items')
        ->innerJoin( $this->getAlias().'.items','items')
        ->innerJoin('items.groupItems', 'groupItems')
        ->where('groupItems.id = :groupItemsId')        
        ->andWhere('items.active = 1')        
        ->setParameter('groupItemsId', $groupId)
        ->orderBy($this->getAlias().'.name', 'ASC');

        $categories = $qb->getQuery()->getArrayResult();

        foreach($categories as $key => $category){
            $categories[$key]['count_items'] = count($category['items']);
        }

        return $categories;
    }

    private function _setChildren($parent, $allCategories){
        $children = [];
        /* Get Children of this category */  
        foreach( $allCategories as $key=>$value ){
            if( $value['parentId'] == $parent['id'] ){
                $children[] = $value;
            }
        }

        /* Add Children for all child */ 
        foreach($children as $key=>$child){
            $children[$key]['children'] = $this->_setChildren($children[$key],  $allCategories);
        }        
        return $children;
    }

    private function _setCounts($allCategories, $categories){
        foreach($categories as $category){
            $path = $category['path'];
            $parentIds = explode('-', $path);            
            foreach($parentIds as $parentId){
                if(isset($allCategories[$parentId])){
                    $allCategories[$parentId]['count_items'] += $category['count_items'];                    
                }
            }
        }
        return $allCategories;
    }


    /**
     * @param $platform
     * @param $user
     * @param $groupItems
     * @return array
     */
    private function filterByClientStatus($platform, $client, $groupItems): array
    {
        $clientStatus = $client->getClientStatusByPlatform($platform->getId());
        if(!$clientStatus){
            return [];
        }
        $clientStatusPreorder = $clientStatus->getStatusPreorder();
        $clientStatusCatalog = $clientStatus->getStatusCatalog();

        $groupItems = $groupItems->filter(
            function ($elem) use ($clientStatusPreorder, $clientStatusCatalog) {

                if ($clientStatusPreorder == ClientStatus::STATUS_PREORDER_BLOCKED) {
                    /** @var GroupItem $elem */
                    if ($elem->getStatus() == GroupItem::STATUS_PREORDER) {

                        return false;
                    }
                }

                if ($clientStatusCatalog == ClientStatus::STATUS_CATALOG_BLOCKED) {
                    if ($elem->getStatus() == GroupItem::STATUS_CATALOG) {

                        return false;
                    }
                }

                return true;
            }
        );

        return  array_values($groupItems->toArray());
    }
}
