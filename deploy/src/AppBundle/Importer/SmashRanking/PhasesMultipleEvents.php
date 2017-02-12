<?php

declare(strict_types=1);

namespace AppBundle\Importer\SmashRanking;

use CoreBundle\Entity\Event;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Tournament;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PhasesMultipleEvents extends AbstractScenario
{
    /**
     * @return void
     */
    public function importWithConfiguration()
    {
        $this->import(true, true, true);
    }

    /**
     * @param array $events
     * @param array $tournaments
     */
    protected function processEvents(array $events, array $tournaments)
    {
        foreach ($events as $tournamentId => $tournament) {
            /** @var Tournament $tournamentEntity */
            $tournamentEntity = $tournaments[$tournamentId];

            $this->io->comment($tournamentEntity->getName());

            $tournament['events'] = $this->filterIntermediateAndAmateurBrackets(
                $tournament['events'],
                $tournamentEntity
            );

            $eventCount = count($tournament['events']);

            if ($eventCount === 0) {
                $this->io->text("There's nothing here anymore...");

                continue;
            } elseif ($eventCount === 1) {
                $this->io->text('Processing this event using the single event (default) scenario...');

                parent::processEvents([
                    $tournamentId => $tournament,
                ], $tournaments);

                continue;
            } elseif ($eventCount === 2) {
                $this->io->text('Processing this tournament using the two events method...');

                $this->processTournamentWithTwoEvents($tournament, $tournamentEntity);

                continue;
            } else {
                $this->io->text('Processing this tournament using the pools with bracket method...');

                $this->processTournamentWithPoolsAndBracket($tournament, $tournamentEntity);
            }
        }
    }

    /**
     * @param array $events
     * @param Tournament $tournament
     * @return array
     */
    protected function filterIntermediateAndAmateurBrackets(array $events, Tournament $tournament)
    {
        return array_filter($events, function ($event, $eventId) use ($tournament) {
            if ($event['type'] === 5 || $event['type'] === 6) {
                $this->processAmateurBracketEvent($eventId, $event, $tournament);

                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param mixed      $eventId
     * @param array      $event
     * @param Tournament $tournamentEntity
     */
    protected function processAmateurBracketEvent($eventId, array $event, Tournament $tournamentEntity)
    {
        $name = 'Melee Singles Amateur Bracket';

        if ($event['type'] === 5) {
            $name = 'Melee Singles Intermediate Bracket';
        }

        $eventEntity = new Event();
        $eventEntity->setName($name);
        $eventEntity->setGame($this->melee);
        $eventEntity->setTournament($tournamentEntity);

        $phase = new Phase();
        $phase->setName('Bracket');
        $phase->setEvent($eventEntity);
        $phase->setPhaseOrder(1);

        $typeId = $this->eventTypes[$event['type']]['newTypeId'];
        $resultsUrl = $event['result_page'] ? $event['result_page'] : null;

        $phaseGroup = new PhaseGroup();
        $phaseGroup->setName($name);
        $phaseGroup->setType($typeId);
        $phaseGroup->setPhase($phase);
        $phaseGroup->setResultsUrl($resultsUrl);

        $this->entityManager->persist($eventEntity);
        $this->entityManager->persist($phase);
        $this->entityManager->persist($phaseGroup);

        $this->phaseGroups[$eventId] = $phaseGroup;
    }

    /**
     * @param array $tournament
     * @param Tournament $tournamentEntity
     *
     * @TODO Implement this method.
     */
    protected function processTournamentWithTwoEvents(array $tournament, Tournament $tournamentEntity)
    {

    }

    /**
     * @param array $tournament
     * @param Tournament $tournamentEntity
     */
    protected function processTournamentWithPoolsAndBracket(array $tournament, Tournament $tournamentEntity)
    {
        $eventEntity = new Event();
        $eventEntity->setName('Melee Singles');
        $eventEntity->setGame($this->melee);
        $eventEntity->setTournament($tournamentEntity);

        $this->entityManager->persist($eventEntity);

        // Create the pool related entities.
        $poolsPhase = new Phase();
        $poolsPhase->setName('Pools');
        $poolsPhase->setEvent($eventEntity);
        $poolsPhase->setPhaseOrder(1);

        $this->entityManager->persist($poolsPhase);

        $pools = array_filter($tournament['events'], function ($event) {
            return $event['type'] !== 4;
        });

        foreach ($pools as $eventId => $event) {
            $name = 'Unnamed pool';

            if ($event['pool']) {
                $name = $event['pool'];
            }

            $typeId = $this->eventTypes[$event['type']]['newTypeId'];
            $resultsUrl = $event['result_page'] ? $event['result_page'] : null;

            $phaseGroup = new PhaseGroup();
            $phaseGroup->setName($name);
            $phaseGroup->setType($typeId);
            $phaseGroup->setPhase($poolsPhase);
            $phaseGroup->setResultsUrl($resultsUrl);

            $this->entityManager->persist($phaseGroup);

            $this->phaseGroups[$eventId] = $phaseGroup;
        }

        // Create the bracket related entities.
        $bracketEvent = current(array_filter($tournament['events'], function ($event) {
            return $event['type'] === 4;
        }));
        $bracketEventId = array_search($bracketEvent, $tournament['events']);

        $name = 'Bracket';

        if ($bracketEvent['name_bracket']) {
            $name = $bracketEvent['name_bracket'];
        };

        $bracketPhase = new Phase();
        $bracketPhase->setName($name);
        $bracketPhase->setEvent($eventEntity);
        $bracketPhase->setPhaseOrder(2);

        $this->entityManager->persist($bracketPhase);

        $typeId = $this->eventTypes[$bracketEvent['type']]['newTypeId'];
        $resultsUrl = $bracketEvent['result_page'] ? $bracketEvent['result_page'] : null;

        $phaseGroup = new PhaseGroup();
        $phaseGroup->setName($name);
        $phaseGroup->setType($typeId);
        $phaseGroup->setPhase($bracketPhase);
        $phaseGroup->setResultsUrl($resultsUrl);

        $this->entityManager->persist($phaseGroup);

        $this->phaseGroups[$bracketEventId] = $phaseGroup;
    }
}