<?php

declare(strict_types = 1);

namespace CoreBundle\Repository;

use CoreBundle\Entity\PhaseGroup;
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
            ->select('pg, s, en1, pl1, en2, pl2, ph, ev, t')
            ->join('pg.sets', 's')
            ->join('s.entrantOne', 'en1')
            ->join('en1.players', 'pl1')
            ->join('s.entrantTwo', 'en2')
            ->join('en2.players', 'pl2')
            ->join('pg.phase', 'ph')
            ->join('ph.event', 'ev')
            ->join('ev.tournament', 't')
            ->where('pg.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
