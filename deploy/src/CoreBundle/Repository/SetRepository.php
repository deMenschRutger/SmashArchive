<?php

declare(strict_types=1);

namespace CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SetRepository extends EntityRepository
{
    /**
     * @param string $slug
     * @return array
     *
     * @TODO When ordering the sets by round, it doesn't take into account that losers bracket matches happen after winners bracket matches.
     */
    public function findByPlayerSlug(string $slug)
    {
        /** @var EntrantRepository $singlePlayerEntrants */
        $entrantRepository = $this->_em->getRepository('CoreBundle:Entrant');

        $singlePlayerEntrantIds = $entrantRepository->findSinglePlayerEntrantIdsBySlug($slug);

        return $this
            ->createQueryBuilder('s')
            ->select('s, pg, ph, ev, t, e1, e2, w, l')
            ->join('s.phaseGroup', 'pg')
            ->join('pg.phase', 'ph')
            ->join('ph.event', 'ev')
            ->join('ev.tournament', 't')
            ->join('s.entrantOne', 'e1')
            ->join('s.entrantTwo', 'e2')
            ->join('s.winner', 'w')
            ->join('s.loser', 'l')
            ->where('e1.id IN (:ids)')
            ->orWhere('e2.id IN (:ids)')
            ->setParameter('ids', $singlePlayerEntrantIds)
            ->setParameter('ids', $singlePlayerEntrantIds)
            ->orderBy('t.id, ev.id, ph.phaseOrder, s.round')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param int $playerOneId
     * @param int $playerTwoId
     * @return array
     */
    public function findHeadToHeadSets(int $playerOneId, int $playerTwoId)
    {
        $entrants = [$playerOneId, $playerTwoId];

        $entrantsIdsQuery = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('e.id')
            ->from('CoreBundle:Entrant', 'e')
            ->join('e.players', 'p')
            ->where('p.id IN (?1)')
        ;

        $queryBuilder = $this
            ->getEntityManager()
            ->createQueryBuilder()
        ;

        $entrantsSinglePlayerIds = $queryBuilder
            ->select('e2.id')
            ->from('CoreBundle:Entrant', 'e2')
            ->join('e2.players', 'p2')
            ->where(
                $queryBuilder->expr()->in('e2.id', $entrantsIdsQuery->getDQL())
            )
            ->groupBy('e2.id')
            ->having('COUNT(p2.id) = 1')
            ->setParameter(1, $entrants)
            ->getQuery()
            ->getResult()
        ;

        $entrantsSinglePlayerIds = array_map(function ($value) {
            return $value['id'];
        }, $entrantsSinglePlayerIds);

        return $this
            ->createQueryBuilder('s')
            ->select('s')
            ->join('s.entrantOne', 'e1')
            ->join('s.entrantTwo', 'e2')
            ->where('e1.id IN (?1)')
            ->andWhere('e2.id IN (?2)')
            ->setParameter(1, $entrantsSinglePlayerIds)
            ->setParameter(2, $entrantsSinglePlayerIds)
            ->getQuery()
            ->getResult()
        ;
    }
}
