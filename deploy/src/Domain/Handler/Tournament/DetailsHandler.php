<?php

declare(strict_types=1);

namespace Domain\Handler\Tournament;

use CoreBundle\DataTransferObject\TournamentDTO;
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
     * @return TournamentDTO
     */
    public function handle(DetailsCommand $command)
    {
        $tournament = $this->getRepository('CoreBundle:Tournament')->findOneBy([
            'slug' => $command->getSlug(),
        ]);

        if (!$tournament instanceof Tournament) {
            throw new NotFoundHttpException();
        }

        return new TournamentDTO($tournament);
    }
}
