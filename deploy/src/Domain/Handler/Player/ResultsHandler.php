<?php

declare(strict_types = 1);

namespace Domain\Handler\Player;

use CoreBundle\Entity\Event;
use CoreBundle\Entity\Result;
use CoreBundle\Entity\Set;
use CoreBundle\Entity\Tournament;
use CoreBundle\Repository\ResultRepository;
use Domain\Command\Player\ResultsCommand;
use Domain\Handler\AbstractHandler;
use Knp\Component\Pager\Pagination\SlidingPagination;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsHandler extends AbstractHandler
{
    /**
     * @var array
     */
    protected $events = [];

    /**
     * @var array
     */
    protected $setsByEventId = [];

    /**
     * @param ResultsCommand $command
     * @return array
     */
    public function handle(ResultsCommand $command)
    {
        /** @var ResultRepository $repository */
        $repository = $this->getRepository('CoreBundle:Result');

        /** @var SlidingPagination $sets */
        $sets = $command->getSets();
        $slug = $command->getSlug();

        if (!$sets) {
            return $repository->findByPlayerSlug($slug);
        }

        $cacheKey = 'player_results_'.$slug;

        if ($this->isCached($cacheKey)) {
            return $this->getFromCache($cacheKey);
        }

        $this->setsByEventId = $this->getSetsByEventId($sets->getItems());

        $results = $repository->findByPlayerSlug($slug);
        $resultsByEvent = $this->processResults($results);
        $remainingResults = $this->processSetsWithoutResult();
        $fullResults = array_merge($resultsByEvent, $remainingResults);

        $tags = [ 'player_'.$slug ];
        $this->saveToCache($cacheKey, $fullResults, $tags);

        return $fullResults;
    }

    /**
     * @param array $sets
     * @return array
     */
    protected function getSetsByEventId(array $sets)
    {
        $setsByEventId = [];

        /** @var Set[] $sets */
        foreach ($sets as $set) {
            $phaseGroup = $set->getPhaseGroup();

            $phase = $phaseGroup->getPhase();
            $phaseId = $phase->getId();

            $event = $phase->getEvent();
            $eventId = $event->getId();
            $this->events[$eventId] = $event;

            if (!array_key_exists($eventId, $setsByEventId)) {
                $setsByEventId[$eventId] = [];
            }

            if (!array_key_exists($phaseId, $setsByEventId[$eventId])) {
                $setsByEventId[$eventId][$phaseId] = [
                    'name' => $phase->getName(),
                    'sets'  => [],
                ];
            }

            $setsByEventId[$eventId][$phaseId]['sets'][] = $set;
        }

        return $setsByEventId;
    }

    /**
     * @param array $results
     * @return array
     */
    protected function processResults($results)
    {
        $resultsByEvent = [];

        /** @var Result $result */
        foreach ($results as $result) {
            $event = $result->getEvent();
            $eventId = $event->getId();

            $tournament = $event->getTournament();
            $setsByPhase = null;

            if (array_key_exists($eventId, $this->setsByEventId)) {
                $setsByPhase = $this->setsByEventId[$eventId];

                unset($this->setsByEventId[$eventId]);
            }

            $resultsByEvent[$eventId] = [
                'tournament'  => $tournament,
                'event'       => $event,
                'rank'        => $result->getRank(),
                'setsByPhase' => $setsByPhase,
            ];
        }

        return $resultsByEvent;
    }

    /**
     * @return array
     */
    protected function processSetsWithoutResult()
    {
        $resultsByEvent = [];

        foreach ($this->setsByEventId as $eventId => $setsByPhase) {
            $tournament = $this->getTournamentByEventId($eventId);
            $event = $this->getEventById($eventId);

            $resultsByEvent[$eventId] = [
                'tournament'  => $tournament,
                'event'       => $event,
                'rank'        => null,
                'setsByPhase' => $setsByPhase,
            ];
        }

        return $resultsByEvent;
    }

    /**
     * @param int $eventId
     * @return Event|null
     */
    protected function getEventById($eventId)
    {
        if (array_key_exists($eventId, $this->events)) {
            return $this->events[$eventId];
        }

        return null;
    }

    /**
     * @param int $eventId
     * @return Tournament|null
     */
    protected function getTournamentByEventId($eventId)
    {
        $event = $this->getEventById($eventId);

        if ($event instanceof Event) {
            return $event->getTournament();
        }

        return null;
    }
}
