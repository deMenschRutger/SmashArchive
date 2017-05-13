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
