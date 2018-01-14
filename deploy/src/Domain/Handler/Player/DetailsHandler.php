<?php

declare(strict_types = 1);

namespace Domain\Handler\Player;

use CoreBundle\Entity\PlayerProfile;
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
     * @return PlayerProfile
     */
    public function handle(DetailsCommand $command)
    {
        $profile = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('p, c, m, s, gm, sm')
            ->from('CoreBundle:PlayerProfile', 'p')
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

        if (!$profile instanceof PlayerProfile) {
            throw new NotFoundHttpException('The player could not be found.');
        }

        return $profile;
    }
}
