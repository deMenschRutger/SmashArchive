<?php

declare(strict_types = 1);

namespace Domain\Handler\Tournament;

use CoreBundle\Entity\Tournament;
use Domain\Command\Tournament\DetailsCommand;
use Domain\Handler\AbstractHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class DetailsHandler extends AbstractHandler
{
    /**
     * @param DetailsCommand $command
     * @return Tournament
     */
    public function handle(DetailsCommand $command)
    {
        // TODO This is a relatively heavy query and a candidate for caching.
        $queryBuilder = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('t, c, e, g, p, pg')
            ->from('CoreBundle:Tournament', 't')
            ->leftJoin('t.country', 'c')
            ->leftJoin('t.events', 'e')
            ->leftJoin('e.game', 'g')
            ->leftJoin('e.phases', 'p')
            ->leftJoin('p.phaseGroups', 'pg')
            ->where('t.slug = :slug')
            ->setParameter('slug', $command->getSlug())
            ->orderBy('e.name, p.phaseOrder')
        ;

        if ($command->getIncludeResults()) {
            $queryBuilder
                ->addSelect('r, en, pl')
                ->leftJoin('e.results', 'r')
                ->leftJoin('r.entrant', 'en')
                ->leftJoin('en.players', 'pl')
                ->addOrderBy('r.rank, en.name')
            ;
        }

        $tournament = $queryBuilder->getQuery()->getOneOrNullResult();

        if (!$tournament instanceof Tournament) {
            throw new NotFoundHttpException('The tournament could not be found.');
        }

        return $tournament;
    }
}
