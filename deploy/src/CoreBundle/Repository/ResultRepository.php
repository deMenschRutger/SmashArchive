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
            ->select('r, en, p')
            ->from('CoreBundle:Result', 'r')
            ->join('r.entrant', 'en')
            ->join('en.players', 'p')
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
            ->select('r, en, p')
            ->from('CoreBundle:Result', 'r')
            ->join('r.entrant', 'en')
            ->join('en.players', 'p')
            ->join('r.event', 'e')
            ->where('e.id = :id')
            ->setParameter('id', $eventId)
            ->orderBy('r.rank, en.name')
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param string|array $slugs
     * @return array
     */
    public function findByPlayerSlug($slugs)
    {
        /** @var EntrantRepository $singlePlayerEntrants */
        $entrantRepository = $this->_em->getRepository('CoreBundle:Entrant');
        $singlePlayerEntrantIds = $entrantRepository->findSinglePlayerEntrantIdsBySlug($slugs);

        return $this
            ->_em
            ->createQueryBuilder()
            ->select('r, en, p')
            ->from('CoreBundle:Result', 'r')
            ->leftJoin('r.event', 'ev')
            ->leftJoin('ev.tournament', 't')
            ->leftJoin('r.entrant', 'en')
            ->leftJoin('en.players', 'p')
            ->where('en.id IN (:ids)')
            ->orderBy('t.dateStart', 'DESC')
            ->setParameter('ids', $singlePlayerEntrantIds)
            ->getQuery()
            ->getResult()
        ;
    }
}
