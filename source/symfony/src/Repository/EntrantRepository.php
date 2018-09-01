<?php

declare(strict_types = 1);

namespace App\Repository;

use App\Entity\Entrant;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EntrantRepository extends EntityRepository
{
    /**
     * @param int    $eventId
     * @param string $name
     * @param array  $exclude
     * @return Query
     */
    public function findByEventId($eventId, $name = null, $exclude = null)
    {
        $queryBuilder = $this
            ->createQueryBuilder('en')
            ->select('en')
            ->join('en.originPhase', 'p')
            ->join('p.event', 'e')
            ->where('e.id = :eventId')
            ->setParameter('eventId', $eventId)
        ;

        if ($name) {
            $queryBuilder
                ->andWhere('en.name LIKE :name')
                ->setParameter('name', "%{$name}%")
            ;
        }

        if (is_array($exclude)) {
            $queryBuilder
                ->andWhere('p.id NOT IN (:exclude)')
                ->setParameter('exclude', $exclude)
            ;
        }

        return $queryBuilder->getQuery();
    }

    /**
     * @param string $slug
     * @param int    $eventId
     * @return Entrant[]
     */
    public function findByProfileSlug($slug, $eventId = null)
    {
        $ids = $this->findIdByProfileSlugs($slug, 'all');

        $queryBuilder = $this
            ->createQueryBuilder('en')
            ->select('en, pl, pp, ph, ev, t')
            ->join('en.players', 'pl')
            ->join('pl.playerProfile', 'pp')
            ->join('en.originPhase', 'ph')
            ->join('ph.event', 'ev')
            ->join('ev.tournament', 't')
            ->where('en.id IN (:ids)')
            ->orderBy('t.dateStart', 'DESC')
            ->setParameter('ids', $ids)
        ;

        if ($eventId) {
            $queryBuilder->andWhere('ev.id = :eventId')->setParameter('eventId', $eventId);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param string $slug
     * @return Entrant
     */
    public function findFirstByPlayerSlug($slug)
    {
        return $this
            ->createQueryBuilder('en')
            ->select('en, pl, pp, ph, ev, t')
            ->join('en.players', 'pl')
            ->join('pl.playerProfile', 'pp')
            ->join('en.originPhase', 'ph')
            ->join('ph.event', 'ev')
            ->join('ev.tournament', 't')
            ->where('pp.slug = :slug')
            ->orderBy('t.dateStart', 'ASC')
            ->setParameter('slug', $slug)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param string|array $slugs
     * @param string       $eventType
     * @return array
     */
    public function findIdByProfileSlugs($slugs, $eventType)
    {
        if (!is_array($slugs)) {
            $slugs = [$slugs];
        }

        $entrantIdsQuery = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('e.id')
            ->from('App:Entrant', 'e')
            ->leftJoin('e.players', 'p')
            ->leftJoin('p.playerProfile', 'pp')
            ->groupBy('e.id')
            ->where('pp.slug IN (:slugs)')
        ;

        if ($eventType !== 'singles' && $eventType !== 'teams') {
            return $entrantIdsQuery
                ->setParameter('slugs', $slugs)
                ->getQuery()
                ->getResult()
            ;
        }

        $queryBuilder = $this->_em->createQueryBuilder();
        $queryBuilder
            ->select('e2.id')
            ->from('App:Entrant', 'e2')
            ->leftJoin('e2.players', 'p2')
            ->where(
                $queryBuilder->expr()->in('e2.id', $entrantIdsQuery->getDQL())
            )
            ->groupBy('e2.id')
            ->setParameter('slugs', $slugs)
        ;

        if ($eventType === 'singles') {
            $queryBuilder->having('COUNT(p2.id) = 1');
        } elseif ($eventType === 'teams') {
            $queryBuilder->having('COUNT(p2.id) = 2');
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
