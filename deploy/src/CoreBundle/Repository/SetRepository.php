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
     * @param string|array $slugs
     * @return array
     *
     * @TODO When ordering the sets by round, it doesn't take into account that losers bracket matches happen after winners bracket matches.
     */
    public function findByPlayerSlug($slugs)
    {
        /** @var EntrantRepository $singlePlayerEntrants */
        $entrantRepository = $this->_em->getRepository('CoreBundle:Entrant');
        $singlePlayerEntrantIds = $entrantRepository->findSinglePlayerEntrantIdsBySlug($slugs);

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
            ->orderBy('t.id, ev.id, ph.phaseOrder, s.round')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string $playerOneSlug
     * @param string $playerTwoSlug
     * @return array
     */
    public function findHeadToHeadSets(string $playerOneSlug, string $playerTwoSlug)
    {
        /** @var EntrantRepository $singlePlayerEntrants */
        $entrantRepository = $this->_em->getRepository('CoreBundle:Entrant');
        $singlePlayerEntrantIds = $entrantRepository
            ->findSinglePlayerEntrantIdsBySlug([$playerOneSlug, $playerTwoSlug])
        ;

        return $this
            ->createQueryBuilder('s')
            ->select('s, e1, e2, w, l, p1, p2')
            ->join('s.entrantOne', 'e1')
            ->join('s.entrantTwo', 'e2')
            ->join('s.winner', 'w')
            ->join('s.loser', 'l')
            ->join('e1.players', 'p1')
            ->join('e2.players', 'p2')
            ->where('e1.id IN (?1)')
            ->andWhere('e2.id IN (?2)')
            ->setParameter(1, $singlePlayerEntrantIds)
            ->setParameter(2, $singlePlayerEntrantIds)
            ->getQuery()
            ->getResult()
        ;
    }
}
