<?php

declare(strict_types=1);

/*
 * (c) 2021 Michael Joyce <mjoyce@sfu.ca>
 * This source file is subject to the GPL v2, bundled
 * with this source code in the file LICENSE.
 */

namespace App\Repository;

use App\Entity\EventCategory;
use Doctrine\Persistence\ManagerRegistry;
use Nines\UtilBundle\Repository\TermRepository;

/**
 * @method null|EventCategory find($id, $lockMode = null, $lockVersion = null)
 * @method null|EventCategory findOneBy(array $criteria, array $orderBy = null)
 * @method EventCategory[] findAll()
 * @method EventCategory[] findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class EventCategoryRepository extends TermRepository {
    public function __construct(ManagerRegistry $registry) {
        parent::__construct($registry, EventCategory::class);
    }
}
