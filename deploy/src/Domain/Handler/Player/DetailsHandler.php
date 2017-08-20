<?php

declare(strict_types = 1);

namespace Domain\Handler\Player;

use CoreBundle\Entity\Player;
use Domain\Command\Player\DetailsCommand;
use Domain\Handler\AbstractHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class DetailsHandler extends AbstractHandler
{
    /**
     * @param DetailsCommand $command
     * @return Player
     */
    public function handle(DetailsCommand $command)
    {
        $player = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('p, c, m, s, gm, sm')
            ->from('CoreBundle:Player', 'p')
            ->leftJoin('p.country', 'c')
            ->leftJoin('p.mains', 'm')
            ->leftJoin('m.game', 'gm')
            ->leftJoin('p.secondaries', 's')
            ->leftJoin('s.game', 'sm')
            ->where('p.slug = :slug')
            ->setParameter('slug', $command->getSlug())
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$player instanceof Player) {
            throw new NotFoundHttpException('The player could not be found.');
        }

        return $player;
    }
}
