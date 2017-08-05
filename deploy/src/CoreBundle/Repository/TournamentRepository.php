<?php

declare(strict_types = 1);

namespace CoreBundle\Repository;

use CoreBundle\Entity\Tournament;
use Doctrine\ORM\EntityRepository;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class TournamentRepository extends EntityRepository
{
    /**
     * @param string $slug
     * @return \Doctrine\ORM\QueryBuilder
     *
     * @TODO This is a relatively heavy query and a candidate for caching.
     */
    public function findWithDetails($slug)
    {
        return $this
            ->_em
            ->createQueryBuilder()
            ->select('t, e, c, g, p, pg')
            ->from('CoreBundle:Tournament', 't')
            ->leftJoin('t.country', 'c')
            ->leftJoin('t.events', 'e')
            ->leftJoin('e.game', 'g')
            ->leftJoin('e.phases', 'p')
            ->leftJoin('p.phaseGroups', 'pg')
            ->where('t.slug = :slug')
            ->setParameter('slug', $slug)
            ->orderBy('e.name, p.phaseOrder')
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param int $tournamentId
     * @return int
     */
    public function getEntrantCount($tournamentId)
    {
        $queryBuilder = $this->_em->createQueryBuilder();

        return (int) $queryBuilder
            ->select('COUNT(DISTINCT p.id)')
            ->from('CoreBundle:Entrant', 'en')
            ->join('en.entrantOneSets', 's1')
            ->join('en.entrantTwoSets', 's2')
            ->join('s1.phaseGroup', 'pg1')
            ->join('s2.phaseGroup', 'pg2')
            ->join('pg1.phase', 'p1')
            ->join('pg2.phase', 'p2')
            ->join('p1.event', 'e1')
            ->join('p2.event', 'e2')
            ->join('e1.tournament', 't1')
            ->join('e2.tournament', 't2')
            ->join('en.players', 'p')
            ->where($queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq('t1.id', $tournamentId),
                $queryBuilder->expr()->eq('t2.id', $tournamentId)
            ))
            ->getQuery()
            ->getSingleScalarResult()
        ;
    }

    /**
     * @param Tournament $tournament
     */
    public function setEntrantCount(Tournament $tournament)
    {
        $entrantCount = $this->getEntrantCount($tournament->getId());
        $tournament->setEntrantCount($entrantCount);
    }
}
