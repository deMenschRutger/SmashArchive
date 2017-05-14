<?php

declare(strict_types = 1);

namespace Domain\Handler\Event;

use CoreBundle\Bracket\SingleElimination\Bracket as SingleEliminationBracket;
use CoreBundle\Bracket\DoubleElimination\Bracket as DoubleEliminationBracket;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Repository\EventRepository;
use Domain\Command\Event\GenerateResultsCommand;
use Domain\Handler\AbstractHandler;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class GenerateResultsHandler extends AbstractHandler
{
    /**
     * @param GenerateResultsCommand $command
     *
     * @TODO Add the combined results of all phases and phase groups and determine the final results.
     */
    public function handle(GenerateResultsCommand $command)
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getRepository('CoreBundle:Event');
        $phases = $eventRepository->getOrderedPhases($command->getEventId());

        /** @var Phase $phase */
        $phase = $phases[0];

        /** @var PhaseGroup $phaseGroup */
        foreach ($phase->getPhaseGroups() as $phaseGroup) {
            $bracket = null;

            switch ($phaseGroup->getType()) {
                case PhaseGroup::TYPE_SINGLE_ELIMINATION:
                    $bracket = new SingleEliminationBracket($phaseGroup);
                    break;

                case PhaseGroup::TYPE_DOUBLE_ELIMINATION:
                    $bracket = new DoubleEliminationBracket($phaseGroup);
                    break;

                default:
                    continue;
            }

            $results = $bracket->getResults();
        }
    }
}
