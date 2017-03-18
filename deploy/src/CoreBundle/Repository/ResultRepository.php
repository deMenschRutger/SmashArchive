<?php

declare(strict_types=1);

namespace CoreBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultRepository extends EntityRepository
{
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
            ->select('r')
            ->from('CoreBundle:Result', 'r')
            ->join('r.event', 'ev')
            ->join('ev.tournament', 't')
            ->join('r.entrant', 'en')
            ->where('en.id IN (:ids)')
            ->orderBy('t.dateStart', 'DESC')
            ->setParameter('ids', $singlePlayerEntrantIds)
            ->getQuery()
            ->getResult()
        ;
    }
}
