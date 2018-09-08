<?php

declare(strict_types = 1);

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class RankRepository extends EntityRepository
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
            ->select('r, en, pl, pr')
            ->from('App:Rank', 'r')
            ->join('r.entrant', 'en')
            ->leftJoin('en.players', 'pl')
            ->leftJoin('pl.profile', 'pr')
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
            ->select('r, en, pl, pr')
            ->from('App:Rank', 'r')
            ->join('r.event', 'e')
            ->join('r.entrant', 'en')
            ->leftJoin('en.players', 'pl')
            ->leftJoin('pl.profile', 'pr')
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
            ->select('r, en, pl, pr')
            ->from('App:Rank', 'r')
            ->join('r.entrant', 'en')
            ->join('en.players', 'pl')
            ->join('pl.profile', 'pr')
            ->join('r.event', 'ev')
            ->where('pr.slug = :slug')
            ->setParameter('slug', $slug)
            ->orderBy('r.rank, en.name')
        ;

        if ($eventId) {
            $queryBuilder->andWhere('ev.id = :eventId')->setParameter('eventId', $eventId);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
