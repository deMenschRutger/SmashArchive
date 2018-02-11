<?php

declare(strict_types = 1);

namespace CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultRepository extends EntityRepository
{
    /**
     * @param int $tournamentId
     * @return array
     */
    public function findForTournament($tournamentId)
    {
        return $this
            ->_em
            ->createQueryBuilder()
            ->select('r, en, pl, pp')
            ->from('CoreBundle:Result', 'r')
            ->join('r.entrant', 'en')
            ->leftJoin('en.players', 'pl')
            ->leftJoin('pl.playerProfile', 'pp')
            ->join('r.event', 'e')
            ->join('e.tournament', 't')
            ->where('t.id = :id')
            ->setParameter('id', $tournamentId)
            ->orderBy('r.rank, en.name')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param int $eventId
     * @return array
     */
    public function findForEvent($eventId)
    {
        return $this
            ->_em
            ->createQueryBuilder()
            ->select('r, en, pl, pp')
            ->from('CoreBundle:Result', 'r')
            ->join('r.entrant', 'en')
            ->join('en.players', 'pl')
            ->join('pl.playerProfile', 'pp')
            ->join('r.event', 'e')
            ->where('e.id = :id')
            ->setParameter('id', $eventId)
            ->orderBy('r.rank, en.name')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string $slug
     * @param int    $eventId
     * @return array
     */
    public function findForProfile($slug, $eventId = null)
    {
        $queryBuilder = $this
            ->_em
            ->createQueryBuilder()
            ->select('r, en, pl, pp')
            ->from('CoreBundle:Result', 'r')
            ->join('r.entrant', 'en')
            ->join('en.players', 'pl')
            ->join('pl.playerProfile', 'pp')
            ->join('r.event', 'ev')
            ->where('pp.slug = :slug')
            ->setParameter('slug', $slug)
            ->orderBy('r.rank, en.name')
        ;

        if ($eventId) {
            $queryBuilder->andWhere('ev.id = :eventId')->setParameter('eventId', $eventId);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
