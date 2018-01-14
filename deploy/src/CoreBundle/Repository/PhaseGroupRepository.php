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
            ->select('pg, s, en1, pl1, pp1, en2, pl2, pp2, ph, ev, t')
            ->leftJoin('pg.sets', 's')
            ->leftJoin('s.entrantOne', 'en1')
            ->leftJoin('en1.players', 'pl1')
            ->leftJoin('pl1.playerProfile', 'pp1')
            ->leftJoin('s.entrantTwo', 'en2')
            ->leftJoin('en2.players', 'pl2')
            ->leftJoin('pl2.playerProfile', 'pp2')
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
