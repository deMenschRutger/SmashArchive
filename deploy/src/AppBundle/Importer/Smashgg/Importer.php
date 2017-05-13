<?php

declare(strict_types = 1);

namespace AppBundle\Importer\Smashgg;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Event;
use CoreBundle\Entity\Game;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Player;
use CoreBundle\Entity\Set;
use CoreBundle\Entity\Tournament;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use GuzzleHttp\Client;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Importer
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Tournament
     */
    protected $tournament;

    /**
     * @var array
     */
    protected $games;

    /**
     * @var array
     */
    protected $events;

    /**
     * @var array
     */
    protected $phases;

    /**
     * @var array
     */
    protected $phaseGroups;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Example slugs:
     *
     * 'tournament/arcamelee-1'
     * 'tournament/syndicate-2016'
     * 'tournament/garelaf-x'
     *
     * @param string $slug
     */
    public function import(string $slug)
    {
        $client = new Client();
        $response = $client->get('https://api.smash.gg/'.$slug, [
            'query' => [
                'expand' => ['event', 'phase', 'groups'],
            ],
        ]);

        $apiData = \GuzzleHttp\json_decode($response->getBody(), true);
        $tournamentData = $apiData['entities']['tournament'];

        $this->tournament = $this->findTournament($slug);
        $this->tournament->setName($tournamentData['name']);

        $this->processGames($apiData['entities']['videogame']);
        $this->processEvents($apiData['entities']['event']);
        $this->processPhases($apiData['entities']['phase']);
        $this->processPhaseGroups($apiData['entities']['groups']);
    }

    /**
     * @param array $apiData
     */
    protected function processGames(array $apiData)
    {
        $games = [];

        foreach ($apiData as $gameData) {
            $gameId = $gameData['id'];
            $game = $this->findGame($gameId);
            $game->setName($gameData['name']);
            $game->setDisplayName($gameData['displayName']);

            $games[$gameId] = $game;
        }

        $this->games = $games;
    }

    /**
     * @param array $apiData
     */
    protected function processEvents(array $apiData)
    {
        $events = [];

        foreach ($apiData as $eventData) {
            $eventId = $eventData['id'];
            $event = $this->findEvent($eventId, $this->tournament);
            $event->setName($eventData['name']);
            $event->setDescription($eventData['description']);
            $event->setTournament($this->tournament);

            $game = $this->games[$eventData['videogameId']];
            $event->setGame($game);

            $events[$eventId] = $event;
        }

        $this->events = $events;
    }


    /**
     * @param array $apiData
     */
    protected function processPhases(array $apiData)
    {
        $phases = [];

        foreach ($apiData as $phaseData) {
            $phaseId = $phaseData['id'];
            $phase = $this->findPhase($phaseId);
            $phase->setName($phaseData['name']);
            $phase->setPhaseOrder($phaseData['phaseOrder']);

            $event = $this->events[$phaseData['eventId']];
            $phase->setEvent($event);

            $phases[$phaseId] = $phase;
        }

        $this->phases = $phases;
    }

    /**
     * @param array $apiData
     */
    protected function processPhaseGroups(array $apiData)
    {
        foreach ($apiData as $phaseGroupData) {
            $phaseGroupId = $phaseGroupData['id'];
            $phaseGroup = $this->findPhaseGroup($phaseGroupId);
            $phaseGroup->setName($phaseGroupData['displayIdentifier']);
            $phaseGroup->setType($phaseGroupData['groupTypeId']);

            $phase = $this->phases[$phaseGroupData['phaseId']];
            $phaseGroup->setPhase($phase);

            // TODO Process the phase groups (see TournamentImportCommand).
            // $this->processPhaseGroup($phaseGroupId, $phaseGroup);
        }
    }

    /**
     * @param string $entityName
     * @return EntityRepository
     */
    protected function getRepository(string $entityName): EntityRepository
    {
        return $this->entityManager->getRepository($entityName);
    }

    /**
     * @param string $slug
     * @return Tournament
     */
    protected function findTournament(string $slug): Tournament
    {
        $tournament = $this->getRepository('CoreBundle:Tournament')->findOneBy([
            'smashggSlug' => $slug,
        ]);

        if (!$tournament instanceof Tournament) {
            $tournament = new Tournament();
            $tournament->setSmashggSlug($slug);

            $this->entityManager->persist($tournament);
        }

        return $tournament;
    }

    /**
     * @param int        $smashGgId
     * @param Tournament $tournament
     * @return Event
     */
    protected function findEvent(int $smashGgId, Tournament $tournament): Event
    {
        $event = $this->getRepository('CoreBundle:Event')->findOneBy([
            'smashggId' => $smashGgId,
            'tournament' => $tournament,
        ]);

        if (!$event instanceof Event) {
            $event = new Event();
            $event->setSmashggId($smashGgId);

            $this->entityManager->persist($event);
        }

        return $event;
    }

    /**
     * @param int $smashGgId
     * @return Game
     */
    protected function findGame(int $smashGgId): Game
    {
        $game = $this->getRepository('CoreBundle:Game')->findOneBy([
            'smashggId' => $smashGgId,
        ]);

        if (!$game instanceof Game) {
            $game = new Game();
            $game->setSmashggId($smashGgId);

            $this->entityManager->persist($game);
        }

        return $game;
    }

    /**
     * @param int $smashGgId
     * @return Phase
     */
    protected function findPhase(int $smashGgId): Phase
    {
        $phase = $this->getRepository('CoreBundle:Phase')->findOneBy([
            'smashggId' => $smashGgId,
        ]);

        if (!$phase instanceof Phase) {
            $phase = new Phase();
            $phase->setSmashggId($smashGgId);

            $this->entityManager->persist($phase);
        }

        return $phase;
    }

    /**
     * @param int $smashGgId
     * @return PhaseGroup
     */
    protected function findPhaseGroup(int $smashGgId): PhaseGroup
    {
        $phaseGroup = $this->getRepository('CoreBundle:PhaseGroup')->findOneBy([
            'smashggId' => $smashGgId,
        ]);

        if (!$phaseGroup instanceof PhaseGroup) {
            $phaseGroup = new PhaseGroup();
            $phaseGroup->setSmashggId($smashGgId);

            $this->entityManager->persist($phaseGroup);
        }

        return $phaseGroup;
    }

    /**
     * @param int $smashGgId
     * @return Set
     */
    protected function findSet(int $smashGgId): Set
    {
        $set = $this->getRepository('CoreBundle:Set')->findOneBy([
            'smashggId' => $smashGgId,
        ]);

        if (!$set instanceof Set) {
            $set = new Set();
            $set->setSmashggId($smashGgId);

            $this->entityManager->persist($set);
        }

        return $set;
    }

    /**
     * @param int $smashGgId
     * @return Player
     */
    protected function findPlayer(int $smashGgId): Player
    {
        $player = $this->getRepository('CoreBundle:Player')->findOneBy([
            'smashggId' => $smashGgId,
        ]);

        if (!$player instanceof Player) {
            $player = new Player();
            $player->setSmashggId($smashGgId);

            $this->entityManager->persist($player);
        }

        return $player;
    }

    /**
     * @param int $smashGgId
     * @return Entrant
     */
    protected function findEntrant(int $smashGgId): Entrant
    {
        $entrant = $this->getRepository('CoreBundle:Entrant')->findOneBy([
            'smashggId' => $smashGgId,
        ]);

        if (!$entrant instanceof Entrant) {
            $entrant = new Entrant();
            $entrant->setSmashggId($smashGgId);

            $this->entityManager->persist($entrant);
        }

        return $entrant;
    }
}
