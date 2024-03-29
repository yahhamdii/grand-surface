<?php

namespace App\Repository;

use Doctrine\ORM\QueryBuilder;
use App\Entity\GroupItem;
use Sogedial\OAuthBundle\Entity\UserCustomer;


/**
 * GroupRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GroupRepository extends EntityRepository
{
    protected $alias = 'g';

    public function findBy(array $filter, array $orderBy = null, $limit = null, $offset = null, $type = null)
    {

        $qb = $this->createFindByQueryBuilder($filter, $orderBy, $limit, $offset, $type);

        $qb = $this->addTypeQueryPart($qb, $type);

        $this->getAcl($qb);

        return $qb->getQuery()->getResult();
    }

    public function getCount(array $filter, $type = null)
    {
        $qb = $this->createCountQueryBuilder($filter);

        $qb = $this->addTypeQueryPart($qb, $type);

        return $qb->getQuery()->getOneOrNullResult();
    }

    public function processCriteria($qb, $filter)
    {
        $sanitizedFilter = $filter;
        unset($sanitizedFilter['client']);
        parent::processCriteria($qb, $sanitizedFilter);
    }

    protected function getAcl(QueryBuilder $qb): void
    {
        $user = $this->getTokenStorage()->getToken()->getUser();
        $date = new \Datetime('now');

        if ($user instanceof UserCustomer) {
            $qb->join('gi.groupClients', 'groupClients');
            $qb->join('groupClients.clients', 'clients', 'WITH', $qb->expr()->eq('clients', ':client'));
            $qb->setParameter('client', $user->getClient());
            $qb->andWhere($qb->expr()->lte(':date', 'gi.dateEnd'))
                ->setParameter('date', $date)
                ->andWhere('gi.enabled = :active ')
                ->setParameter('active', true);
        }
    }

    protected function addSpecificQuery(array $filter, QueryBuilder $qb, $type)
    {
        if ($type === 'item') {
            if (isset($filter['client'])) {
                $qb->join('gi.groupClients', 'groupClients')
                    ->join('groupClients.clients', 'clients', 'WITH', $qb->expr()->eq('clients', ':client'))
                    ->setParameter('client', $filter['client']);
            }
        }
    }


    protected function createFindByQueryBuilder(array $filter, array $orderBy = null, $limit = null, $offset = null, $type = null)
    {

        $qb = parent::createFindByQueryBuilder($filter, $orderBy, $limit, $offset);

        if ($type == 'item') {
            $qb->join(GroupItem::class, 'gi', 'WITH', $qb->expr()->eq($this->alias . '.id', 'gi.id'));
        }

        $this->addSpecificQuery($filter, $qb, $type);

        return $qb;
    }
}