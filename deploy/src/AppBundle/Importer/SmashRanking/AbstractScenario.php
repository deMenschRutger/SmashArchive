<?php

declare(strict_types=1);

namespace AppBundle\Importer\SmashRanking;

use AppBundle\Command\SmashRankingImportCommand;
use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Event;
use CoreBundle\Entity\Game;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Player;
use CoreBundle\Entity\Set;
use CoreBundle\Entity\Tournament;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @TODO Take into account 'forfeit' and 'publish' on the match model.
 */
abstract class AbstractScenario
{
    /**
     * @var array
     *
     * Set the brackets to single elimination first, and to double elimination later when detected.
     */
    protected $eventTypes = [
        1 => [
            'originalName' => 'Swiss System',
            'eventName' => 'Melee Singles',
            'newTypeId' => PhaseGroup::TYPE_SWISS,
        ],
        2 => [
            'originalName' => 'Round Robin Pool',
            'eventName' => 'Melee Singles',
            'newTypeId' => PhaseGroup::TYPE_ROUND_ROBIN,
        ],
        3 => [
            'originalName' => 'Bracket pool',
            'eventName' => 'Melee Singles',
            'newTypeId' => PhaseGroup::TYPE_SINGLE_ELIMINATION,
        ],
        4 => [
            'originalName' => 'Bracket',
            'eventName' => 'Melee Singles',
            'newTypeId' => PhaseGroup::TYPE_SINGLE_ELIMINATION,
        ],
        5 => [
            'originalName' => 'Intermediate Bracket',
            'eventName' => 'Melee Singles Intermediate Bracket',
            'newTypeId' => PhaseGroup::TYPE_SINGLE_ELIMINATION,
        ],
        6 => [
            'originalName' => 'Amateur Bracket',
            'eventName' => 'Melee Singles Amateur Bracket',
            'newTypeId' => PhaseGroup::TYPE_SINGLE_ELIMINATION,
        ],
    ];

    /**
     * @var array
     */
    protected $rounds = [
        1 => 1, // 'W1'
        2 => 2, // 'W2'
        3 => 3, // 'W3'
        4 => 4, // 'W4'
        5 => 5, // 'W5'
        6 => 6, // 'W6'
        7 => 7, // 'W7'
        8 => 8, // 'W8'
        9 => 9, // 'L1'
        10 => -1, // 'L2'
        11 => -2, // 'L3'
        12 => -3, // 'L4'
        13 => -4, // 'L5'
        14 => -5, // 'L6'
        15 => -6, // 'L7'
        16 => -7, // 'L8'
        17 => -8, // 'L9'
        18 => -9, // 'L10'
        19 => -10, // 'L11'
        20 => -11, // 'L12'
        21 => -12, // 'L13'
        22 => -13, // 'L14'
        23 => 1, // 'R1',
        24 => 2, // 'R2',
        25 => 3, // 'R3',
        26 => 4, // 'R4',
        27 => 5, // 'R5',
        28 => 6, // 'R6',
        29 => 7, // 'R7',
        30 => 8, // 'R8',
        31 => 9, // 'R9',
        32 => 10, // 'R10',
        33 => 10, // 'GF1'
        34 => 11, // 'GF2'
    ];

    /**
     * @var string
     */
    protected $defaultPhaseName = 'Bracket';

    /**
     * @var SmashRankingImportCommand
     */
    protected $importer;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Game
     */
    protected $melee;

    /**
     * @var array
     */
    protected $eventsPerTournament;

    /**
     * @var array
     */
    protected $phaseGroups = [];

    /**
     * @var array
     */
    protected $entrants;

    /**
     * @param Importer      $importer
     * @param SymfonyStyle  $io
     * @param EntityManager $entityManager
     */
    public function __construct(Importer $importer, SymfonyStyle $io, EntityManager $entityManager) {
        $this->importer = $importer;
        $this->io = $io;
        $this->entityManager = $entityManager;
        $this->melee = $this->entityManager->find('CoreBundle:Game', 1);
    }

    /**
     * @return void
     */
    abstract public function importWithConfiguration();

    /**
     * @param bool $hasPhases
     * @param bool $hasMultipleEvents
     * @param bool $isBracket
     */
    public function import(bool $hasPhases, bool $hasMultipleEvents, bool $isBracket)
    {
        $this->io->text('Importing events...');
        $this->eventsPerTournament = $this->getEventsPerTournament();
        $this->io->text(
            sprintf('Found %d tournaments with events before phase filtering.', count($this->eventsPerTournament))
        );

        $this->io->text(sprintf('Filtering events based on scenario...', count($this->eventsPerTournament)));
        $this->filterEvents($hasPhases, $hasMultipleEvents, $isBracket);

//        $this->io->text('Processing events...');
//        $this->processEvents($events);

//        $this->io->text('Flushing entity manager...');
//        $this->entityManager->flush();

//        $this->io->text('Processing phase groups...');
//        $this->processPhaseGroups($this->phaseGroups);

//        $this->entityManager->flush();
    }

    /**
     * Steps:
     *
     * 1 Tournament has phases or no phases?
     * 1.1 Phases -> 2 (count: 190)
     * 1.2 No Phases -> 2 (count: 1341)
     *
     * 2 No phases: Single event or multiple events?
     * 2.1 Single event -> 3 (count: 1068 + 76 = 1144)
     * 2.2 Multiple events -> Phases not marked correctly, needs more custom importing logic (count: 273 + 114 = 387)
     *
     * 3 Single event: is it a bracket (type 4, 5 or 6)?
     * 3.1 No -> Round robin pools, can be imported, but placings are determined differently (count: 11 + 1)
     * 3.2 Yes -> Can be imported (count: 1057 + 75)
     *
     * @return array
     */
    protected function getEventsPerTournament()
    {
        $events = $this->importer->getContentFromJson('event');
        $eventsPerTournament = [];

        foreach ($events as $eventId => $event) {
            $tournamentId = $event['tournament'];
            $this->importer->getTournamentById($tournamentId); // Asserts that the tournament exists.

            if (!array_key_exists($tournamentId, $eventsPerTournament)) {
                $eventsPerTournament[$tournamentId] = [
                    'events' => [],
                    'hasPhases' => false,
                ];
            }

            $eventsPerTournament[$tournamentId]['events'][$eventId] = $event;

            if ($event['phase'] !== null) {
                $eventsPerTournament[$tournamentId]['hasPhases'] = true;
            }
        }

        return $eventsPerTournament;
    }

    /**
     * @param bool $hasPhases
     * @param bool $hasMultipleEvents
     * @param bool $isBracket
     * @return array
     */
    protected function filterEvents(bool $hasPhases, bool $hasMultipleEvents, bool $isBracket)
    {
        $eventsPerTournament = array_filter($this->eventsPerTournament, function ($tournament) use ($hasPhases) {
            if ($hasPhases) {
                return $tournament['hasPhases'];
            }

            return !$tournament['hasPhases'];
        });

        $this->io->text(sprintf('Found %d tournaments after phase filtering.', count($eventsPerTournament)));

        $eventsPerTournament = array_filter($eventsPerTournament, function ($tournament) use ($hasMultipleEvents) {
            Assert::greaterThan(count($tournament['events']), 0);

            if ($hasMultipleEvents) {
                return count($tournament['events']) > 1;
            }

            return count($tournament['events']) === 1;
        });

        $this->io->text(sprintf('Found %d tournaments after event count filtering.', count($eventsPerTournament)));

        if (!$hasMultipleEvents) {
            $eventsPerTournament = array_filter($eventsPerTournament, function ($tournament) use ($isBracket) {
                $eventTypes = [1, 2, 3];

                if ($isBracket) {
                    $eventTypes = [4, 5, 6];
                }

                foreach ($tournament['events'] as $eventId => $event) {
                    Assert::greaterThanEq($event['type'], 1);
                    Assert::lessThanEq($event['type'], 6);

                    if (!in_array($event['type'], $eventTypes)) {
                        return false;
                    }
                }

                return true;
            });

            $this->io->text(sprintf('Found %d tournaments after event type filtering.', count($eventsPerTournament)));
        }

        return $eventsPerTournament;
    }

    /**
     * @param array $events
     * @param array $tournaments
     *
     * Assumptions:
     *
     * - If there is a 'name_bracket', it is the name of a phase (top 64 etc.). This phase will have only on phase group (a bracket).
     * - If the event is of type 5 or 6 it is a different event (intermediate and amateur brackets respectively).
     * - Intermediate and amateur brackets never have more than one phase.
     * - If the event has a truthy value in the 'pool' field, it is a phase group part of a phase preceding a bracket or subsequent pool phase.
     * - If the event is a pool, it will not have a value in the 'name_bracket' field, and the name of the pool will be in the 'pool' field.
     */
    protected function processEvents(array $events, array $tournaments)
    {
        foreach ($events as $tournamentId => $tournament) {
            /** @var Tournament $tournamentEntity */
            $tournamentEntity = $tournaments[$tournamentId];

            $this->io->comment($tournamentEntity->getName());

            foreach ($tournament['events'] as $eventId => $event) {
                $eventName = $this->eventTypes[$event['type']]['eventName'];

                $entity = new Event();
                $entity->setName($eventName);
                $entity->setGame($this->melee);
                $entity->setTournament($tournamentEntity);

                $this->entityManager->persist($entity);

                $phaseName = $this->defaultPhaseName;

                if ($event['name_bracket']) {
                    $phaseName = $event['name_bracket'];
                }

                $phase = new Phase();
                $phase->setName($phaseName);
                $phase->setEvent($entity);
                $phase->setPhaseOrder(1);

                $this->entityManager->persist($phase);

                $resultsUrl = $event['result_page'] ? $event['result_page'] : null;
                $phaseGroupType = $this->eventTypes[$event['type']]['newTypeId'];

                $phaseGroup = new PhaseGroup();
                $phaseGroup->setName($phaseName);
                $phaseGroup->setType($phaseGroupType);
                $phaseGroup->setPhase($phase);
                $phaseGroup->setResultsUrl($resultsUrl);

                $this->entityManager->persist($phaseGroup);

                $this->phaseGroups[$eventId] = $phaseGroup;
            }
        }
    }

    /**
     * @param array $phaseGroups
     */
    protected function processPhaseGroups(array $phaseGroups)
    {
        if (count($this->players) === 0) {
            $this->players = $this->getContentFromJson('smasher');
        }

        $matches = $this->getContentFromJson('match');
        $counter = 0;

        foreach ($matches as $matchId => $match) {
            $eventId = $match['event'];

            if (!array_key_exists($eventId, $phaseGroups)) {
                continue;
            }

            /** @var PhaseGroup $phaseGroup */
            $phaseGroup = $phaseGroups[$eventId];
            $event = $phaseGroup->getPhase()->getEvent();
            $tournamentId = $event->getTournament()->getId();
            $entrantOne = $this->getEntrant($match['winner'], $tournamentId);
            $entrantTwo = $this->getEntrant($match['loser'], $tournamentId);

            $round = $match['round'];

            if ($round === null) {
                $round = 1;
            } elseif ($round > 8 && $round < 23 && $phaseGroup->getType() === PhaseGroup::TYPE_SINGLE_ELIMINATION) {
                // If the round goes above 8 it means we have a match in the losers bracket, therefore this is a double
                // elimination bracket.
                $phaseGroup->setType(PhaseGroup::TYPE_DOUBLE_ELIMINATION);
            }

            $round = $this->rounds[$round];

            $set = new Set();
            $set->setPhaseGroup($phaseGroup);
            $set->setRound($round);
            $set->setEntrantOne($entrantOne);
            $set->setEntrantTwo($entrantTwo);
            $set->setWinner($entrantOne);
            $set->setLoser($entrantTwo);

            $this->entityManager->persist($set);

            $counter++;
        }

        $this->io->text("Counted {$counter} sets.");
    }

    /**
     * @param integer $playerId
     * @param integer $tournamentId
     * @return Entrant|bool
     */
    protected function getEntrant($playerId, $tournamentId)
    {
        if (isset($this->entrants[$tournamentId][$playerId])) {
            return $this->entrants[$tournamentId][$playerId];
        }

        if (!array_key_exists($playerId, $this->players)) {
            return false;
        }

        if (array_key_exists('entity', $this->players[$playerId])) {
            $player = $this->players[$playerId]['entity'];
        } else {
            $tag = $this->players[$playerId]['tag'];
            $player = new Player();
            $player->setGamerTag($tag);

            $this->entityManager->persist($player);

            $this->players[$playerId]['entity'] = $player;
        }

        $entrant = new Entrant();
        $entrant->setName($player->getGamerTag());
        $entrant->addPlayer($player);
        $player->addEntrant($entrant);

        $this->entityManager->persist($entrant);

        $this->entrants[$tournamentId][$playerId] = $entrant;

        return $entrant;
    }
}