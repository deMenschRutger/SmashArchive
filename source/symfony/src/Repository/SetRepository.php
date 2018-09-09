<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Entrant;
use App\Entity\Set;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SetRepository extends EntityRepository
{
    /**
     * @param string $entrantId
     * @param string $phaseId
     *
     * @return Set[]
     */
    public function findByEntrantIdAndPhaseId($entrantId, $phaseId)
    {
        $queryBuilder = $this->createQueryBuilder('s');

        return $queryBuilder
            ->select('s, pg, ph, e1, e2')
            ->join('s.phaseGroup', 'pg')
            ->join('pg.phase', 'ph')
            ->leftJoin('s.entrantOne', 'e1')
            ->leftJoin('s.entrantTwo', 'e2')
            ->where('ph.id = :phaseId')
            ->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq('e1.id', ':id'),
                $queryBuilder->expr()->eq('e2.id', ':id')
            ))
            ->setParameter('id', $entrantId)
            ->setParameter('phaseId', $phaseId)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param Entrant $entrant
     *
     * @return Set
     */
    public function findFirstByEntrant(Entrant $entrant)
    {
        return $this
            ->createQueryBuilder('s')
            ->select('s, pg, ph, ev, t, e1, e2')
            ->join('s.phaseGroup', 'pg')
            ->join('pg.phase', 'ph')
            ->join('ph.event', 'ev')
            ->join('ev.tournament', 't')
            ->leftJoin('s.entrantOne', 'e1')
            ->leftJoin('s.entrantTwo', 'e2')
            ->where('e1 = :entrant')
            ->orWhere('e2 = :entrant')
            ->orderBy('t.dateStart', 'ASC')
            ->setParameter('entrant', $entrant)
            ->getQuery()
            ->setMaxResults(1)
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param string|array $slugs
     * @param string       $eventType
     *
     * @return Query
     */
    public function findByProfileSlug($slugs, $eventType)
    {
        // Returning the query to accommodate pagination.
        return $this->getProfileSetsQuery($slugs, $eventType)->getQuery();
    }

    /**
     * @param string|array $slugs
     * @param string       $eventId
     *
     * @return Query
     */
    public function findByProfileSlugAndEventId($slugs, $eventId)
    {
        return $this
            ->getProfileSetsQuery($slugs, 'all')
            ->andWhere('ev.id = :eventId')
            ->setParameter('eventId', $eventId)
            ->getQuery()
        ;
    }

    /**
     * @param string $playerOneSlug
     * @param string $playerTwoSlug
     *
     * @return array
     */
    public function findHeadToHeadSets(string $playerOneSlug, string $playerTwoSlug)
    {
        /** @var EntrantRepository $singlePlayerEntrants */
        $entrantRepository = $this->_em->getRepository('App:Entrant');
        $singlePlayerEntrantIds = $entrantRepository->findIdByProfileSlugs([$playerOneSlug, $playerTwoSlug], 'singles');

        return $this
            ->createQueryBuilder('s')
            ->select('s, e1, e2, w, l, p1, p2, pr1, pr2')
            ->join('s.entrantOne', 'e1')
            ->join('s.entrantTwo', 'e2')
            ->join('s.winner', 'w')
            ->join('s.loser', 'l')
            ->join('e1.players', 'p1')
            ->join('p1.profile', 'pr1')
            ->join('e2.players', 'p2')
            ->join('p2.profile', 'pr2')
            ->where('e1.id IN (?1)')
            ->andWhere('e2.id IN (?2)')
            ->setParameter(1, $singlePlayerEntrantIds)
            ->setParameter(2, $singlePlayerEntrantIds)
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string|array $slugs
     * @param string       $eventType
     *
     * @return QueryBuilder
     */
    protected function getProfileSetsQuery($slugs, $eventType)
    {
        /** @var EntrantRepository $entrantRepository */
        $entrantRepository = $this->_em->getRepository('App:Entrant');
        $entrantIds = $entrantRepository->findIdByProfileSlugs($slugs, $eventType);

        return $this
            ->getEntrantSetsQuery($entrantIds)
            ->orderBy('t.dateStart DESC, ev.id, ph.phaseOrder, s.round')
        ;
    }

    /**
     * @param array $entrantIds
     *
     * @return QueryBuilder
     */
    protected function getEntrantSetsQuery(array $entrantIds)
    {
        return $this
            ->createQueryBuilder('s')
            ->select('s, pg, ph, ev, g, t, e1, p1, pr1, e2, p2, pr2, w, l')
            ->join('s.phaseGroup', 'pg')
            ->join('pg.phase', 'ph')
            ->join('ph.event', 'ev')
            ->join('ev.game', 'g')
            ->join('ev.tournament', 't')
            ->leftJoin('s.entrantOne', 'e1')
            ->leftJoin('e1.players', 'p1')
            ->leftJoin('p1.profile', 'pr1')
            ->leftJoin('s.entrantTwo', 'e2')
            // Joining entrant.players here confuses Doctrine for some reason, see Entrant::getPlayers().
            ->leftJoin('e2.players', 'p2')
            ->leftJoin('p2.profile', 'pr2')
            ->leftJoin('s.winner', 'w')
            ->leftJoin('s.loser', 'l')
            ->where('e1.id IN (:ids)')
            ->orWhere('e2.id IN (:ids)')
            ->setParameter('ids', $entrantIds)
        ;
    }
}
