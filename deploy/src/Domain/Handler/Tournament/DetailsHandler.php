<?php

declare(strict_types=1);

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
        $tournament = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('t, e, r, en')
            ->from('CoreBundle:Tournament', 't')
            ->join('t.events', 'e')
            ->leftJoin('e.results', 'r')
            ->leftJoin('r.entrant', 'en')
            ->where('t.slug = :slug')
            ->setParameter('slug', $command->getSlug())
            ->orderBy('e.name, r.rank, en.name')
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$tournament instanceof Tournament) {
            throw new NotFoundHttpException('The tournament could not be found.');
        }

        return $tournament;
    }
}
