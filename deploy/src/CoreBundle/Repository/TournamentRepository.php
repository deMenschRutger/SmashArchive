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
}
