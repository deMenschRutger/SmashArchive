<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\PhaseGroup;
use Doctrine\ORM\EntityRepository;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PhaseGroupRepository extends EntityRepository
{
    /**
     * @param int $id
     * @return PhaseGroup
     */
    public function findWithTournament($id)
    {
        return $this
            ->createQueryBuilder('pg')
            ->select('pg, s, en1, pl1, pr1, en2, pl2, pr2, ph, ev, t')
            ->leftJoin('pg.sets', 's')
            ->leftJoin('s.entrantOne', 'en1')
            ->leftJoin('en1.players', 'pl1')
            ->leftJoin('pl1.profile', 'pr1')
            ->leftJoin('s.entrantTwo', 'en2')
            ->leftJoin('en2.players', 'pl2')
            ->leftJoin('pl2.profile', 'pr2')
            ->leftJoin('pg.phase', 'ph')
            ->leftJoin('ph.event', 'ev')
            ->leftJoin('ev.tournament', 't')
            ->where('pg.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
