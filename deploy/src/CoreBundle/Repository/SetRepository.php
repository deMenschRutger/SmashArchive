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
     * @param int $playerOneId
     * @param int $playerTwoId
     * @return mixed
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
