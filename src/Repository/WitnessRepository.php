<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\Witness;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method null|Witness find($id, $lockMode = null, $lockVersion = null)
 * @method Witness[] findAll()
 * @method Witness[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 * @method null|Witness findOneBy(array $criteria, array $orderBy = null)
 */
class WitnessRepository extends ServiceEntityRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, Witness::class);
    }

    public function indexQuery() : Query {
        return $this->createQueryBuilder('witness')
            ->innerJoin('witness.event', 'event')
            ->orderBy('event.date')
            ->getQuery();
    }
}
