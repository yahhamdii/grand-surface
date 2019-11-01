<?php

namespace App\Repository;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Attribut;
use App\Entity\ClientStatus;
use App\Entity\GroupItem;
use App\Entity\Item;
use App\Entity\Promotion;
use App\Exception\BadRequestException;
use App\Exception\ForbiddenException;
use Sogedial\OAuthBundle\Entity\UserAdmin;
use Sogedial\OAuthBundle\Entity\UserCommercial;
use Sogedial\OAuthBundle\Entity\UserCustomer;

class ItemRepository extends EntityRepository
{

    protected $alias = 'i';

    /**
     * @param $qb
     * @param $filter
     */
    protected function processCriteria($qb, $filter)
    {

        $sanitizedFilter = $filter;

        unset($sanitizedFilter['is_new']);
        unset($sanitizedFilter['has_promotion']);
        unset($sanitizedFilter['is_ordered']);
        unset($sanitizedFilter['groupItem']);
        unset($sanitizedFilter['manufacturer']);
        unset($sanitizedFilter['stock']);
        unset($sanitizedFilter['selection']);

        parent::processCriteria($qb, $sanitizedFilter);
    }

    /**
     * @param array $filter
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @param bool $showManufacturers
     * @param bool $limitManufacturers
     * @return array|mixed|Item[]
     */
    public function findBy(array $filter, array $orderBy = null, $limit = null, $offset = null, $showManufacturers = false, $limitManufacturers = true)
    {

        $qb = $this->createFindByQueryBuilder($filter, $orderBy, $limit, $offset);

        (isset($filter['platform'])) ? $this->getAcl($qb, $filter['platform']) : $this->getAcl($qb);

        $itemResults = $qb->getQuery()->getResult();
        $showManufacturers = filter_var($showManufacturers, FILTER_VALIDATE_BOOLEAN);

        if (!$showManufacturers) {
            return $itemResults;
        }

        return $this->showManufacturers($qb, $itemResults, $limitManufacturers);
    }

    /**
     * @param array $filter
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @return QueryBuilder
     */
    protected function createFindByQueryBuilder(array $filter, array $orderBy = null, $limit = null, $offset = null)
    {

        $qb = parent::createFindByQueryBuilder($filter, $orderBy, $limit, $offset);

        $qb->distinct();

        $this->addSpecificQuery($filter, $qb);

        return $qb;
    }

    /**
     * @param array $filter
     * @return QueryBuilder
     */
    protected function createCountQueryBuilder(array $filter)
    {

        $qb = parent::createCountQueryBuilder($filter);

        $this->addSpecificQuery($filter, $qb);

        return $qb;
    }

    /**
     * @param $q
     * @param array $filter
     * @param array|null $orderBy
     * @param null $limit
     * @param null $offset
     * @param bool $showManufacturers
     * @param bool $limitManufacturers
     * @return array|mixed
     */
    public function getItemSuggestion($q, array $filter, array $orderBy = null, $limit = null, $offset = null, $showManufacturers = true, $limitManufacturers = true)
    {
        $qb = $this->createFindByQueryBuilder($filter, $orderBy, $limit, $offset);

        $this->addSuggestionQuery($q, $qb, $filter);

        $itemResults = $qb->getQuery()->getResult();
        $showManufacturers = filter_var($showManufacturers, FILTER_VALIDATE_BOOLEAN);
        if (!$showManufacturers)
            return $itemResults;

        return $this->showManufacturers($qb, $itemResults, $limitManufacturers);
    }

    /**
     * @param $q
     * @param array $filter
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCountItemSuggestion($q, array $filter)
    {

        $qb = $this->createCountQueryBuilder($filter);

        $this->addSuggestionQuery($q, $qb, $filter);

        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param $q
     * @param $qb
     * @return mixed
     */
    protected function addSuggestionQuery($q, $qb, $filter)
    {
        /** @var QueryBuilder $qb */
        (isset($filter['platform'])) ? $this->getAcl($qb, $filter['platform']) : $this->getAcl($qb);

        $qb->leftJoin('categories.parent', 'family', 'WITH', 'family.id = categories.parent');
        if (!in_array('manufacturer', $qb->getAllAliases())) {
            $qb->leftjoin('p.manufacturer', 'manufacturer');
        }

        $qb->andWhere('REGEXP(i.ean13, :regexp) = true OR REGEXP(i.name, :regexp) = true OR REGEXP(i.reference, :regexp) = true OR REGEXP(manufacturer.name, :regexp) = true OR REGEXP(family.name, :regexp) = true OR REGEXP(categories.name, :regexp) = true')
            ->setParameter('regexp', $this->createRegexp($q));

        return $qb;
    }

    /**
     * @param $filter
     * @param $qb
     * @return mixed
     */
    protected function addSpecificQuery($filter, QueryBuilder $qb)
    {

        $qb->join($this->getAlias() . '.categories', 'categories');

        if (isset($filter['is_new']) && $filter['is_new'] == true) {

            if (!in_array('stock', $qb->getAllAliases())) {
                $qb->join($this->getAlias() . '.stock', 'stock');
            }

            $limit = 90;

            if (isset($filter['platform'])) {
                $platform = $this->findPlatform($filter['platform']);
                if ($platform) {
                    $attribut = $platform->getAttributByKey(Attribut::KEY_NEW);
                    if ($attribut) {
                        if ($attribut->getValue() == 0)
                            $limit = 90;
                        if ($attribut->getValue() == 1)
                            $limit = 60;
                    }
                }
            }

            $dateEntryStockLimit = date("Y-m-d H:i:s", time() - 60 * 60 * 24 * $limit);
            $currentDate= date("Y-m-d H:i:s");
            $qb->andWhere($qb->expr()->orX($qb->expr()->between('stock.firstDateEntryInStock', ':dateEntryStockLimit', ':currentDate'), $qb->expr()->isNull('stock.firstDateEntryInStock')));
            $qb->setParameter('dateEntryStockLimit', $dateEntryStockLimit)
                ->setParameter('currentDate', $currentDate);
        }

        if (isset($filter['has_promotion'])) {
            
            $qb->innerJoin($this->getAlias() . '.promotions', 'promotions');
            $qb->andWhere('promotions.dateStartValidity <= :now');
            $qb->andWhere('promotions.dateEndValidity > :now');
            $qb->andWhere('promotions.displayAsPromotion =  :displayAsPromotion');
            $qb->andWhere('promotions.promoCode IN (:promoCodes)');

            $qb->setParameter('now', date("Y-m-d H:i:s", time()));
            $qb->setParameter('displayAsPromotion', true);
            $qb->setParameter('promoCodes', Promotion::CODES_PRIORITY);            

             if ($this->tokenStorage) {
                $user = $this->tokenStorage->getToken()->getUser();
                $client = $user->getClient();
                
                if (isset($filter['platform'])) {
                    $platform = $this->findPlatform($filter['platform']);
                    $userBrand = $client->getBrandByPlatform($platform);
                    if($userBrand){
                        $qb->andWhere(
                            $qb->expr()->orX(
                                $qb->expr()->isNull('promotions.brand'),
                                $qb->expr()->eq('promotions.brand', ':brand')
                            )
                        );
                        $qb->setParameter('brand', $userBrand);
                    }                    
                }

                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->isNull('promotions.client'), 
                        $qb->expr()->eq('promotions.client', ':client')
                    )
                );             
                $qb->setParameter('client', $client);
            }
        }

        if (isset($filter['groupItem'])) {
            $qb->andWhere($qb->expr()->orX($qb->expr()->in('groupItem.id', ':test'), $qb->expr()->in('groupItem.slug', ':test')))
                ->setParameter('test', $filter['groupItem']);
        }

        if (isset($filter['selection'])) {
            $qb->andWhere($qb->expr()->orX($qb->expr()->in('groupItem.slug', ':test'), $qb->expr()->in('groupItem.id', ':test')))
                ->setParameter('test', $filter['selection']);
        }       

        if (isset($filter['is_ordered'])) {
            $qb->innerJoin($this->getAlias() . '.orderItems', 'orderItem');
            $qb->innerJoin('orderItem.order', 'o', Expr\Join::WITH, $qb->expr()->andX($qb->expr()->eq('o.user', ':user'), $qb->expr()->eq('o.platform', ':platform')))
                ->setParameter('user', $this->getTokenStorage()->getToken()->getUser()->getId())
                ->setParameter('platform', $filter['platform']);
        }

        if (isset($filter['manufacturer'])) {
            $qb->join($this->getAlias() . '.product', 'p');
            $qb->join('p.manufacturer', 'manufacturer', 'WITH', $qb->expr()->in('manufacturer.name', ':manufacturerName'))
                ->setParameter('manufacturerName', $filter['manufacturer']);
        }

        if (isset($filter['stock']) && $filter['stock'] == 'available') {
            if (!in_array('stock', $qb->getAllAliases())) {
                $qb->join($this->getAlias() . '.stock', 'stock');
            }

            $qb->andWhere($qb->expr()->andX($qb->expr()->gt('stock.valueCu', ':minQuantity'), $qb->expr()->gt('stock.valuePacking', ':minQuantity')))
                ->setParameter('minQuantity', 0);
        }

        return $qb;
    }

    /**
     * @param QueryBuilder $qb
     * @param null $idPlatform
     * @throws BadRequestException
     * @throws ForbiddenException
     */
    protected function getAcl($qb, $idPlatform = null): void
    {
        if ($this->tokenStorage) {
            $user = $this->tokenStorage->getToken()->getUser();
        }

        if (!in_array('p', $qb->getAllAliases())) {
            $qb->join($this->alias . '.product', 'p');
        }

        $qb->andWhere($this->alias . '.active = :active')
            ->setParameter('active', true);

        if ($this->tokenStorage && $user instanceof UserCustomer) {

            $client = $user->getClient();

            //utilisations getAllGroupItems(), pour avoir tous les groupItems de tous les groupClients du clients
            $groupItems = $client->getAllGroupItems();

            $ids = [];
            /** @var GroupItem $groupItem */
            foreach ($groupItems as $groupItem) {
                $ids[] = $groupItem->getId();
            }

            if (count($ids) == 0) {
                throw new ForbiddenException('This client does not have any Items');
            }

            $qb->join($this->alias . '.groupItems', 'groupItem', 'WITH', $qb->expr()->in('groupItem.id', $ids));

            $qb->andWhere($qb->expr()->eq('groupItem.platform', ':platform'))
                ->setParameter('platform', $idPlatform);

            $clientStatus = $client->getClientStatusByPlatform($idPlatform);

            if (!$clientStatus) {
                throw new BadRequestException("the client does not have a status in the given platform");
            }

            $statusPreorder = $clientStatus->getStatusPreorder();
            $statusCatalog = $clientStatus->getStatusCatalog();

            if ($statusPreorder != ClientStatus::STATUS_PREORDER_ACTIVE && $statusCatalog != ClientStatus::STATUS_CATALOG_ACTIVE) {
                throw new ForbiddenException('client blocked in this platform !');
            }

            if ($statusCatalog == ClientStatus::STATUS_CATALOG_BLOCKED) {
                $qb->andWhere($qb->expr()->neq('groupItem.status', ':status'))
                    ->setParameter('status', GroupItem::STATUS_CATALOG);
                $this->isPreorderQuery($qb);
            }

            if ($statusPreorder == ClientStatus::STATUS_PREORDER_BLOCKED) {
                $qb->andWhere($qb->expr()->neq('groupItem.status', ':status'))
                    ->setParameter('status', GroupItem::STATUS_PREORDER);
                $this->isPreorderQuery($qb, false);
            }

        } elseif ($this->tokenStorage && ($user instanceof UserCommercial || $user instanceof UserAdmin)) {
            $platform = $user->getPlatform();
            $qb->andWhere($qb->expr()->eq($this->alias . '.platform', ':platform'))
                ->setParameter('platform', $platform->getId());
        }
    }

    /**
     * @param array $filter
     * @return mixed
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getCount(array $filter)
    {
        $qb = $this->createCountQueryBuilder($filter);
        (isset($filter['platform'])) ? $this->getAcl($qb, $filter['platform']) : $this->getAcl($qb);
        return $qb->getQuery()->getOneOrNullResult();
    }

    /**
     * @param QueryBuilder $qb
     * @param bool $isPreorder
     */
    protected function isPreorderQuery(QueryBuilder $qb, $isPreorder = true): void
    {
        if ($isPreorder) {
            $qb->andWhere($qb->expr()->andX($qb->expr()->eq($this->alias . '.codeNature', ':precmdCodeNature'), $qb->expr()->eq($this->alias . '.frequency', ':precmdFrequency')));
        } else {
            $qb->andWhere($qb->expr()->andX($qb->expr()->neq($this->alias . '.codeNature', ':precmdCodeNature'), $qb->expr()->neq($this->alias . '.frequency', ':precmdFrequency')));
        }
        $qb->setParameter('precmdCodeNature', Item::PRECMD_CODE_NATURE)
            ->setParameter('precmdFrequency', '£');
    }

    /**
     * @param QueryBuilder $qb
     * @param $itemResults
     * @param bool $limitManufacturers
     * @return array
     */
    private function showManufacturers(QueryBuilder $qb, $itemResults, $limitManufacturers = true): array
    {
        if (!in_array('manufacturer', $qb->getAllAliases())) {
            $qb->join('p.manufacturer', 'manufacturer');
        }

        $qb->groupBy('manufacturer.id');
        $qb->orderBy('manufacturer.id', 'DESC');
        $qb->select(array(
            'COUNT(manufacturer.id) as numberDataAvailable',
            'manufacturer.id',
            'manufacturer.slug',
            'manufacturer.name',
            'manufacturer.extCode'
        ));


        $limitManufacturers = filter_var($limitManufacturers, FILTER_VALIDATE_BOOLEAN);
        if (!$limitManufacturers) {
            $qb->setFirstResult(null)
                ->setMaxResults(null);
        } else {
            $this->processLimit($qb, 5, 0);
        }

        $manufacturersResults = $qb->getQuery()->getResult();

        return array(
            'manufacturers' => $manufacturersResults,
            'items' => $itemResults,
        );
    }

    protected function processOrderBy($qb, $orderBy)
    {
        if(array_key_exists('random', $orderBy)){
            $qb->addOrderBy('RAND()');
            $orderBy = null;
        }
        parent::processOrderBy($qb, $orderBy);
    }


}
