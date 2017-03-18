<?php

declare(strict_types=1);

namespace Domain\Handler\Player;

use CoreBundle\Entity\Result;
use CoreBundle\Entity\Set;
use CoreBundle\Repository\ResultRepository;
use Domain\Command\Player\ResultsCommand;
use Domain\Handler\AbstractHandler;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsHandler extends AbstractHandler
{
    /**
     * @param ResultsCommand $command
     * @return array
     *
     * @TODO If there are sets without a result (for example because the tournament hasn't finished yet), they won't be returned here.
     */
    public function handle(ResultsCommand $command)
    {
        /** @var ResultRepository $repository */
        $repository = $this->getRepository('CoreBundle:Result');
        $results = $repository->findByPlayerSlug($command->getSlug());
        $sets = $command->getSets();

        if (!$sets) {
            return $results;
        }

        $setsByEventId = $this->getSetsByEventId($sets);
        $resultsByEvent = [];

        /** @var Result $result */
        foreach ($results as $result) {
            $event = $result->getEvent();
            $eventId = $event->getId();

            $tournament = $event->getTournament();
            $eventSets = null;

            if (array_key_exists($eventId, $setsByEventId)) {
                $eventSets = $setsByEventId[$eventId];
            }

            $resultsByEvent[$eventId] = [
                'tournament' => $tournament,
                'event'      => $event,
                'rank'       => $result->getRank(),
                'sets'       => $eventSets,
            ];
        }

        return $resultsByEvent;
    }

    /**
     * @param array $sets
     * @return array
     */
    protected function getSetsByEventId(array $sets)
    {
        $events = [];

        /** @var Set[] $sets */
        foreach ($sets as $set) {
            $phase = $set->getPhaseGroup()->getPhase();
            $phaseId = $phase->getId();

            $event = $phase->getEvent();
            $eventId = $event->getId();

            if (!array_key_exists($eventId, $events)) {
                $events[$eventId]['phases'] = [];
            }

            if (!array_key_exists($phaseId, $events[$eventId]['phases'])) {
                $events[$eventId]['phases'][$phaseId] = [
                    'name' => $phase->getName(),
                    'sets'  => [],
                ];
            }

            $events[$eventId]['phases'][$phaseId]['sets'][] = $set;
        }

        return $events;
    }
}
