<?php

declare(strict_types=1);

namespace AppBundle\Importer\SmashRanking;

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

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @TODO Take into account 'forfeit' and 'publish' on the match model.
 */
abstract class AbstractScenario
{
    /**
     * @var array
     */
    protected $eventTypes = [
        1 => 'Swiss System',
        2 => 'Round Robin Pool',
        3 => 'Bracket Pool',
        4 => 'Bracket',
        5 => 'Intermediate Bracket',
        6 => 'Amateur Bracket',
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
        23 => -14, // 'R1'
        24 => -15, // 'R2'
        25 => -16, // 'R3'
        26 => 'R4',
        27 => 'R5',
        28 => 'R6',
        29 => 'R7',
        30 => 'R8',
        31 => 'R9',
        32 => 'R10',
        33 => 10, // 'GF1'
        34 => 11, // 'GF2'
    ];

    /**
     * @var string
     */
    protected $defaultPhaseGroupsName = 'Bracket';

    /**
     * @var string
     */
    protected $contentDirPath;

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
    protected $entrants;

    /**
     * @var array
     */
    protected $players;

    /**
     * @param string        $contentDirPath
     * @param SymfonyStyle  $io
     * @param EntityManager $entityManager
     */
    public function __construct(string $contentDirPath, SymfonyStyle $io, EntityManager $entityManager)
    {
        $this->contentDirPath = $contentDirPath;
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
        $this->io->text('Importing tournaments...');
        $tournaments = $this->getTournaments();

        $this->io->text('Importing events...');
        $events = $this->getEvents($hasPhases, $hasMultipleEvents, $isBracket);

        $this->io->text('Processing events...');
        $phaseGroups = $this->processEvents($events, $tournaments);

        $this->io->text('Flushing entity manager...');
        $this->entityManager->flush();

        $this->io->text('Processing phase groups...');
        $this->processPhaseGroups($phaseGroups);

        $this->entityManager->flush();
    }

    /**
     * @param string $contentKey
     * @return array
     */
    protected function getContentFromJson(string $contentKey)
    {
        $jsonPath = realpath($this->contentDirPath."/ranking.{$contentKey}.json");
        $json = file_get_contents($jsonPath);

        if (!$json) {
            throw new \InvalidArgumentException("No JSON file found for key {$contentKey}.");
        }

        return \GuzzleHttp\json_decode($json, true);
    }

    /**
     * @return array
     */
    protected function getTournaments()
    {
        $tournaments = $this->getContentFromJson('tournament');

        foreach ($tournaments as $tournamentId => &$tournament) {
            $entity = new Tournament();
            $entity->setName($tournament['name']);

            $this->entityManager->persist($entity);

            $tournament = $entity;
        }

        return $tournaments;
    }

    /**
     * @param bool $hasPhases
     * @param bool $hasMultipleEvents
     * @param bool $isBracket
     * @return array
     */
    protected function getEvents(bool $hasPhases, bool $hasMultipleEvents, bool $isBracket)
    {
        /*
         * 1 Tournament has phases or no phases?
         * 1.1 Phases -> Can be imported (count: 190)
         * 1.2 No Phases -> 2 (count: 1341)
         *
         * 2 No phases: Single event or multiple events?
         * 2.1 Single event -> 3 (count: 1068)
         * 2.2 Multiple events -> 4 (count: 273)
         *
         * 3 Single event: is it a bracket (type 4, 5 or 6)?
         * 3.1 Yes -> Import them all (count: 1057)
         * 3.2 No -> Possible, but placings need to be determined in a different way (count: 11)
         *
         * 4 Multiple events: are they all brackets (type 4, 5 or 6)?
         * 4.1 Yes -> They must be separate events somehow (maybe singles and doubles)?
         * 4.2 No -> We probably have phases that weren't marked as phases.
         */

        $events = $this->getContentFromJson('event');
        $eventsPerTournament = [];

        foreach ($events as $eventId => $event) {
            $tournamentId = $event['tournament'];

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

        $this->io->text(sprintf('Found %d tournaments before phase filtering.', count($eventsPerTournament)));

        $eventsPerTournament = array_filter($eventsPerTournament, function ($tournament) use ($hasPhases) {
            if ($hasPhases) {
                return $tournament['hasPhases'];
            }

            return !$tournament['hasPhases'];
        });

        $this->io->text(sprintf('Found %d tournaments after phase filtering.', count($eventsPerTournament)));

        $eventsPerTournament = array_filter($eventsPerTournament, function ($tournament) use ($hasMultipleEvents) {
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
     * @return array
     */
    protected function processEvents(array $events, array $tournaments)
    {
        $phaseGroups = [];

        foreach ($events as $tournamentId => $tournament) {
            $tournamentEntity = $tournaments[$tournamentId];

            foreach ($tournament['events'] as $eventId => $event) {
                $entity = new Event();
                $entity->setName('Melee Singles');
                $entity->setGame($this->melee);
                $entity->setTournament($tournamentEntity);

                $this->entityManager->persist($entity);

                $phaseName = $this->eventTypes[$event['type']];
                $phase = new Phase();
                $phase->setName($phaseName);
                $phase->setEvent($entity);
                $phase->setPhaseOrder(1);

                $this->entityManager->persist($phase);

                $phaseGroup = new PhaseGroup();
                $phaseGroup->setName($this->defaultPhaseGroupsName);
                $phaseGroup->setType(2);
                $phaseGroup->setPhase($phase);

                $this->entityManager->persist($phaseGroup);

                $phaseGroups[$eventId] = $phaseGroup;
            }
        }

        return $phaseGroups;
    }

    /**
     * @param array $phaseGroups
     */
    protected function processPhaseGroups(array $phaseGroups)
    {
        $matches = $this->getContentFromJson('match');
        $this->players = $this->getContentFromJson('smasher');
        $counter = 0;

        foreach ($matches as $matchId => $match) {
            $eventId = $match['event'];

            if (!array_key_exists($eventId, $phaseGroups)) {
                continue;
            }

            /** @var PhaseGroup $phaseGroup */
            $phaseGroup = $phaseGroups[$eventId];
            $tournamentId = $phaseGroup->getPhase()->getEvent()->getTournament()->getId();
            $entrantOne = $this->getEntrant($match['winner'], $tournamentId);
            $entrantTwo = $this->getEntrant($match['loser'], $tournamentId);

            $round = $match['round'];

            if ($round === null) {
                $round = 1;
            }

            $set = new Set();
            $set->setPhaseGroup($phaseGroup);
            // TODO Map rounds to the way smash.gg uses them.
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