<?php

declare(strict_types = 1);

namespace AppBundle\Importer\SmashRanking;

use AppBundle\Command\SmashRankingImportCommand;
use CoreBundle\Entity\Event;
use CoreBundle\Entity\Game;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Console\Style\SymfonyStyle;
use Webmozart\Assert\Assert;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
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
    protected $eventsPerTournament = [];

    /**
     * @param Importer      $importer
     * @param SymfonyStyle  $io
     * @param EntityManager $entityManager
     */
    public function __construct(Importer $importer, SymfonyStyle $io, EntityManager $entityManager)
    {
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

        $this->io->text('Processing events...');
        $this->processEvents();
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

        $this->eventsPerTournament = $eventsPerTournament;
    }

    /**
     * Assumptions:
     *
     * - If there is a 'name_bracket', it is the name of a phase (top 64 etc.). This phase will have only one phase group (a bracket).
     * - If the event is of type 5 or 6 it is a different event (intermediate and amateur brackets respectively).
     * - Intermediate and amateur brackets never have more than one phase.
     * - If the event has a truthy value in the 'pool' field, it is a phase group part of a phase preceding a bracket or subsequent pool phase.
     * - If the event is a pool, it will not have a value in the 'name_bracket' field, and the name of the pool will be in the 'pool' field.
     *
     * @param array $tournaments
     */
    protected function processEvents($tournaments = null)
    {
        if ($tournaments === null) {
            $tournaments = $this->eventsPerTournament;
        }

        foreach ($tournaments as $tournamentId => $tournament) {
            foreach ($tournament['events'] as $eventId => $event) {
                $eventName = $this->eventTypes[$event['type']]['eventName'];
                $eventEntity = $this->createEventEntity($eventName, $tournamentId);

                $phaseName = $this->defaultPhaseName;

                if ($event['name_bracket']) {
                    $phaseName = $event['name_bracket'];
                }

                $phase = $this->createPhase($phaseName, 1, $eventEntity);

                $this->createPhaseGroup($phaseName, $phase, $eventId, $event);
            }
        }
    }

    /**
     * @param string $name
     * @param int    $tournamentId
     * @return Event
     */
    protected function createEventEntity(string $name, $tournamentId)
    {
        $tournament = $this->importer->getTournamentById($tournamentId);

        $entity = new Event();
        $entity->setName($name);
        $entity->setGame($this->melee);
        $entity->setTournament($tournament);

        $this->entityManager->persist($entity);

        return $entity;
    }

    /**
     * @param string $name
     * @param int    $order
     * @param Event  $event
     * @return Phase
     */
    protected function createPhase(string $name, $order, Event $event)
    {
        $phase = new Phase();
        $phase->setName($name);
        $phase->setEvent($event);
        $phase->setPhaseOrder($order);

        $this->entityManager->persist($phase);

        return $phase;
    }

    /**
     * @param string $name
     * @param int    $eventId
     * @param Phase  $phase
     * @param array  $eventData The original data from the SmashRanking database.
     */
    protected function createPhaseGroup(string $name, Phase $phase, $eventId, array $eventData)
    {
        $phaseGroupType = $this->eventTypes[$eventData['type']]['newTypeId'];
        $resultsPage = $eventData['result_page'] ? $eventData['result_page'] : null;

        $phaseGroup = new PhaseGroup();
        $phaseGroup->setOriginalId($eventId);
        $phaseGroup->setName($name);
        $phaseGroup->setType($phaseGroupType);
        $phaseGroup->setResultsPage($resultsPage);
        $phaseGroup->setPhase($phase);

        $this->entityManager->persist($phaseGroup);

        $this->importer->addPhaseGroup($eventId, $phaseGroup);
    }
}
