<?php

declare(strict_types=1);

namespace CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PlayerRepository extends EntityRepository
{
    /**
     * @param string $slug
     * @return int
     */
    public function findPlayerIdBySlug(string $slug)
    {
        return intval($this
            ->createQueryBuilder('p')
            ->select('p.id')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getSingleScalarResult()
        );
    }

    /**
     * @param string $slug
     * @return array
     *
     * @TODO This can also return results where the entrant consists of more than one player.
     *
     */
    public function findSetsBySlug(string $slug)
    {
        $entrantIds = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('e.id')
            ->from('CoreBundle:Entrant', 'e')
            ->join('e.players', 'p')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->getQuery()
            ->getScalarResult()
        ;

        $entrantIds = array_map(function ($value) {
            return $value['id'];
        }, $entrantIds);

        return $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('
                s.id AS setId,
                en1.id AS entrantOneId,
                en2.id AS entrantTwoId,
                en1.name AS entrantOneName,
                en2.name AS entrantTwoName,
                ew.id AS winnerId,
                el.id AS loserId,
                s.round,
                pg.id AS phaseGroupId,
                ph.id AS phaseId,
                ph.name AS phaseName,
                ev.id AS eventId,
                ev.name AS eventName,
                t.id AS tournamentId,
                t.name AS tournamentName
            ')
            ->from('CoreBundle:Set', 's')
            ->join('s.phaseGroup', 'pg')
            ->join('pg.phase', 'ph')
            ->join('ph.event', 'ev')
            ->join('ev.tournament', 't')
            ->join('s.entrantOne', 'en1')
            ->join('s.entrantTwo', 'en2')
            ->join('s.winner', 'ew')
            ->join('s.loser', 'el')
            ->where('en1.id IN (:entrantIds)')
            ->orWhere('en2.id IN (:entrantIds)')
            ->orderBy('t.id', 'DESC')
            ->addOrderBy('s.round', 'ASC')
            ->setParameter('entrantIds', $entrantIds)
            ->getQuery()
            ->getArrayResult()
        ;
    }
}
