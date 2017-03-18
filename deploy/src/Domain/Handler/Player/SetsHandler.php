<?php

declare(strict_types=1);

namespace Domain\Handler\Player;

use CoreBundle\Entity\Set;
use CoreBundle\Repository\SetRepository;
use Domain\Command\Player\SetsCommand;
use Domain\Handler\AbstractHandler;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SetsHandler extends AbstractHandler
{
    /**
     * @param SetsCommand $command
     * @return array
     *
     * @TODO This information probably needs to be paginated somehow.
     */
    public function handle(SetsCommand $command)
    {
        /** @var SetRepository $repository */
        $repository = $this->getRepository('CoreBundle:Set');
        $sets = $repository->findByPlayerSlug($command->getSlug());

        if ($command->getFormat() === 'tournament') {
            return $this->formatSetsByTournament($sets);
        }

        return $sets;
    }

    /**
     * @param array $sets
     * @return array
     */
    protected function formatSetsByTournament(array $sets)
    {
        $setsByTournament = [];

        /** @var Set[] $sets */
        foreach ($sets as $set) {
            $phase = $set->getPhaseGroup()->getPhase();
            $phaseName = $phase->getName();
            $eventName = $phase->getEvent()->getName();
            $tournamentName = $phase->getEvent()->getTournament()->getName();

            $setsByTournament[$tournamentName][$eventName][$phaseName][] = $set;
        }

        return $setsByTournament;
    }
}
