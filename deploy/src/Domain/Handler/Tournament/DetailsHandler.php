<?php

declare(strict_types = 1);

namespace Domain\Handler\Tournament;

use CoreBundle\Entity\Tournament;
use CoreBundle\Repository\TournamentRepository;
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
        /** @var TournamentRepository $tournamentRepository */
        $tournamentRepository = $this->getRepository('CoreBundle:Tournament');
        $tournament = $tournamentRepository->findWithDetails($command->getSlug());

        if (!$tournament instanceof Tournament) {
            throw new NotFoundHttpException('The tournament could not be found.');
        }

        return $tournament;
    }
}
