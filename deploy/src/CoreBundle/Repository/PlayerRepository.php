<?php

declare(strict_types = 1);

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
}
