<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\SmashRanking;

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
     * @param array $tournaments
     */
    protected function processEvents($tournaments = null)
    {
        foreach ($this->eventsPerTournament as $tournamentId => $tournament) {
            $tournament['events'] = $this->filterIntermediateAndAmateurBrackets($tournament['events']);
            $eventCount = count($tournament['events']);

            if ($eventCount === 1) {
                parent::processEvents([
                    $tournamentId => $tournament,
                ]);

                continue;
            } elseif ($eventCount === 2) {
                $this->processTournamentWithTwoEvents($tournamentId, $tournament);

                continue;
            } elseif ($eventCount > 2) {
                $this->processTournamentWithPoolsAndBracket($tournamentId, $tournament);
            }
        }
    }

    /**
     * @param array $events
     * @return array
     */
    protected function filterIntermediateAndAmateurBrackets(array $events)
    {
        return array_filter($events, function ($event, $eventId) {
            if ($event['type'] === 5 || $event['type'] === 6) {
                $this->processAmateurBracketEvent($eventId, $event);

                return false;
            }

            return true;
        }, ARRAY_FILTER_USE_BOTH);
    }

    /**
     * @param string $eventId
     * @param array  $event
     */
    protected function processAmateurBracketEvent($eventId, array $event)
    {
        $name = 'Melee Singles Amateur Bracket';

        if ($event['type'] === 5) {
            $name = 'Melee Singles Intermediate Bracket';
        }

        $eventEntity = $this->createEventEntity($name, $event['tournament']);

        $phase = $this->createPhase('Bracket', 1, $eventEntity);

        $this->createPhaseGroup($name, $phase, $eventId, $event);
    }

    /**
     * @param int   $tournamentId
     * @param array $tournament
     */
    protected function processTournamentWithTwoEvents($tournamentId, array $tournament)
    {
        uasort($tournament['events'], function ($a, $b) {
            if ($a['type'] === $b['type']) {
                return 0;
            }

            return ($a['type'] < $b['type']) ? -1 : 1;
        });

        $otherEvent = current($tournament['events']);
        $otherEventId = key($tournament['events']);
        $bracketEvent = next($tournament['events']);
        $bracketEventId = key($tournament['events']);

        // Process the bracket.
        $bracketEventEntity = $this->createEventEntity('Melee Singles', $tournamentId);
        $phaseName = 'Bracket';

        if ($bracketEvent['name_bracket']) {
            $phaseName = $bracketEvent['name_bracket'];
        };

        $phase = $this->createPhase($phaseName, 1, $bracketEventEntity);
        $this->createPhaseGroup($phaseName, $phase, $bracketEventId, $bracketEvent);

        // Process the other event.
        if ($otherEvent['type'] === 2) {
            // This is secretly an amateur bracket.
            $otherEvent['type'] = 6;
            $this->processAmateurBracketEvent($otherEventId, $otherEvent);
        } else {
            // This is a Swiss bracket.
            $otherEventEntity = $this->createEventEntity('Melee Singles Swiss', $tournamentId);
            $phaseName = 'Swiss';

            if ($otherEvent['name_bracket']) {
                $phaseName = $otherEvent['name_bracket'];
            };

            $phase = $this->createPhase($phaseName, 1, $otherEventEntity);
            $this->createPhaseGroup($phaseName, $phase, $otherEventId, $otherEvent);
        }
    }

    /**
     * @param int   $tournamentId
     * @param array $tournament
     */
    protected function processTournamentWithPoolsAndBracket($tournamentId, array $tournament)
    {
        $eventEntity = $this->createEventEntity('Melee Singles', $tournamentId);

        // Create the pool related entities.
        $pools = array_filter($tournament['events'], function ($event) {
            return $event['type'] !== 4;
        });

        $poolsPhase = $this->createPhase('Pools', 1, $eventEntity);

        foreach ($pools as $eventId => $event) {
            $poolName = 'Unnamed pool';

            if ($event['pool']) {
                $poolName = $event['pool'];
            }

            $this->createPhaseGroup($poolName, $poolsPhase, $eventId, $event);
        }

        // Create the bracket related entities.
        $bracketEvent = current(array_filter($tournament['events'], function ($event) {
            return $event['type'] === 4;
        }));
        $bracketEventId = array_search($bracketEvent, $tournament['events']);

        $bracketName = 'Bracket';

        if ($bracketEvent['name_bracket']) {
            $bracketName = $bracketEvent['name_bracket'];
        };

        $bracketPhase = $this->createPhase($bracketName, 2, $eventEntity);
        $this->createPhaseGroup($bracketName, $bracketPhase, $bracketEventId, $bracketEvent);
    }
}
