<?php

declare(strict_types = 1);

namespace Domain\Handler\Tournament;

use CoreBundle\Entity\Result;
use CoreBundle\Repository\ResultRepository;
use Domain\Command\Tournament\ResultsCommand;
use Domain\Handler\AbstractHandler;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsHandler extends AbstractHandler
{
    /**
     * @param ResultsCommand $command
     * @return array
     */
    public function handle(ResultsCommand $command)
    {
        /** @var ResultRepository $resultRepository */
        $resultRepository = $this->getRepository('CoreBundle:Result');
        $tournamentId = $command->getTournamentId();
        $eventId = $command->getEventId();

        if ($tournamentId) {
            $resultsPerEvent = [];
            $results = $resultRepository->findForTournament($tournamentId);

            /** @var Result $result */
            foreach ($results as $result) {
                $eventId = $result->getEvent()->getId();

                if (!array_key_exists($eventId, $resultsPerEvent)) {
                    $resultsPerEvent[$eventId] = [];
                }

                $resultsPerEvent[$eventId][] = $result;
            }

            return $resultsPerEvent;
        } elseif ($eventId) {
            return $resultRepository->findForEvent($eventId);
        }

        return [];
    }
}
