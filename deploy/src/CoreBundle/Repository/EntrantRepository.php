<?php

declare(strict_types=1);

namespace CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EntrantRepository extends EntityRepository
{
    /**
     * @param string|array $slugs
     * @return array
     */
    public function findSinglePlayerEntrantIdsBySlug($slugs)
    {
        if (!is_array($slugs)) {
            $slugs = [$slugs];
        }

        $entrantIdsQuery = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('e.id')
            ->from('CoreBundle:Entrant', 'e')
            ->leftJoin('e.players', 'p')
            ->groupBy('e.id')
            ->where('p.slug IN (:slugs)')
        ;

        $queryBuilder = $this->_em->createQueryBuilder();

        $singlePlayerEntrants = $queryBuilder
            ->select('e2.id')
            ->from('CoreBundle:Entrant', 'e2')
            ->leftJoin('e2.players', 'p2')
            ->where(
                $queryBuilder->expr()->in('e2.id', $entrantIdsQuery->getDQL())
            )
            ->setParameter('slugs', $slugs)
            ->groupBy('e2.id')
            ->having('COUNT(p2.id) = 1')
            ->getQuery()
            ->getResult()
        ;

        return array_map(function ($value) {
            return $value['id'];
        }, $singlePlayerEntrants);
    }
}
