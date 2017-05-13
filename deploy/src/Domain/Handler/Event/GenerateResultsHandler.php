<?php

declare(strict_types = 1);

namespace Domain\Handler\Event;

use CoreBundle\Bracket\SingleElimination\ResultsGenerator as SingleEliminationResultsGenerator;
use CoreBundle\Bracket\DoubleElimination\ResultsGenerator as DoubleEliminationResultsGenerator;
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
     */
    public function handle(GenerateResultsCommand $command)
    {
        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getRepository('CoreBundle:Event');
        $phases = $eventRepository->getOrderedPhases($command->getEventId());

        /** @var Phase $phase */
        $phase = $phases[2];

        /** @var PhaseGroup $phaseGroup */
        foreach ($phase->getPhaseGroups() as $phaseGroup) {
            switch ($phaseGroup->getType()) {
                case PhaseGroup::TYPE_SINGLE_ELIMINATION:
                    $resultsGenerator = new SingleEliminationResultsGenerator($phaseGroup);
                    break;

                case PhaseGroup::TYPE_DOUBLE_ELIMINATION:
                    $resultsGenerator = new DoubleEliminationResultsGenerator($phaseGroup);
                    break;

                default:
                    continue;
            }
        }
    }
}
