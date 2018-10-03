<?php

namespace AppBundle\Repository;

/**
 * RelationshipCategoryRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RelationshipCategoryRepository extends \Doctrine\ORM\EntityRepository
{
    public function typeaheadQuery($q) {
        $qb = $this->createQueryBuilder('e');
        $qb->andWhere("e.label LIKE :q");
        $qb->orderBy('e.label');
        $qb->setParameter('q', "{$q}%");
        return $qb->getQuery()->execute();
    }
}
