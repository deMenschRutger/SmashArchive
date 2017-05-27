<?php

declare(strict_types = 1);

namespace Domain\Handler\Tournament\Import;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Event;
use CoreBundle\Entity\Game;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Player;
use CoreBundle\Entity\Set;
use CoreBundle\Entity\Tournament;
use CoreBundle\Service\Smashgg\Smashgg;
use Domain\Command\Event\GenerateResultsCommand;
use Domain\Command\Tournament\Import\SmashggCommand;
use Domain\Handler\AbstractHandler;
use League\Tactician\CommandBus;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @TODO Find a way to preserve the phase group ID when re-importing events (for SEO purposes).
 */
class SmashggHandler extends AbstractHandler
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var Smashgg
     */
    protected $smashgg;

    /**
     * @var Tournament
     */
    protected $tournament;

    /**
     * @var array
     */
    protected $games = [];

    /**
     * @var array
     */
    protected $events = [];

    /**
     * @var array
     */
    protected $phases = [];

    /**
     * @var array
     */
    protected $phaseGroups = [];

    /**
     * @var array
     */
    protected $players = [];

    /**
     * @var array
     */
    protected $entrants = [];

    /**
     * @return Smashgg
     */
    public function getSmashgg()
    {
        return $this->smashgg;
    }

    /**
     * @param Smashgg $smashgg
     */
    public function setSmashgg(Smashgg $smashgg)
    {
        $this->smashgg = $smashgg;
    }

    /**
     * @return CommandBus
     */
    public function getCommandBus()
    {
        return $this->commandBus;
    }

    /**
     * @param CommandBus $commandBus
     */
    public function setCommandBus(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * Example slugs:
     *
     * 'arcamelee-1'
     * 'syndicate-2016'
     * 'garelaf-x'
     *
     * @param SmashggCommand $command
     *
     * @TODO Update tournament name from data received from smash.gg.
     */
    public function handle(SmashggCommand $command)
    {
        $eventIds = $command->getEventIds();

        $this->tournament = $this->getTournament($command->getSlug());
        $this->handleExistingEvents($eventIds, $command->getForce());

        $this->processGames();
        $this->processEvents($eventIds);
        $this->processPhases();
        $this->processGroups();

        $this->entityManager->flush();

        foreach ($this->tournament->getEvents() as $event) {
            $command = new GenerateResultsCommand($event->getId());
            $this->commandBus->handle($command);
        }
    }

    /**
     * @param string $slug
     * @return Tournament
     *
     * @TODO Check with smash.gg if the tournament is actually complete.
     */
    protected function getTournament($slug)
    {
        $smashggTournament = $this->smashgg->getTournament($slug);
        $tournament = $this->getRepository('CoreBundle:Tournament')->findOneBy([
            'smashggSlug' => $slug,
        ]);

        if (!$tournament instanceof Tournament) {
            $tournament = new Tournament();
            $tournament->setSmashggSlug($slug);
            $tournament->setIsActive(true);
            $tournament->setIsComplete(true);

            $this->entityManager->persist($tournament);
        }

        $tournament->setName($smashggTournament['name']);

        return $tournament;
    }

    /**
     * @param array $eventIds
     * @param bool  $force
     */
    protected function handleExistingEvents(array $eventIds, bool $force = false)
    {
        /** @var Event[] $events */
        $events = $this->getRepository('CoreBundle:Event')->findBy([
            'smashggId' => $eventIds,
        ]);

        if (count($events) > 0 && !$force) {
            $names = [];

            foreach ($events as $event) {
                $names[] = $event->getName();
            }

            $message = join(' ', [
                'The following events already exist in the database: %s. Please add the force flag (-f) if you wish to override these',
                'events with the most recent event data from smash.gg. Please note that this will remove all existing data for the event,',
                'even the data that was modified after the event was originally imported.',
            ]);

            throw new \InvalidArgumentException(sprintf($message, join(', ', $names)));
        }

        foreach ($events as $event) {
            $this->entityManager->remove($event);
        }

        $this->entityManager->flush();
    }

    /**
     * @return void
     */
    protected function processGames()
    {
        $games = $this->smashgg->getTournamentVideogames($this->tournament->getSmashggSlug(), true);

        foreach ($games as $gameData) {
            $gameId = $gameData['id'];

            $game = $this->findGame($gameId);
            $game->setName($gameData['name']);
            $game->setDisplayName($gameData['displayName']);

            $this->games[$gameId] = $game;
        }
    }

    /**
     * @param array $eventIds
     * @return void
     */
    protected function processEvents(array $eventIds)
    {
        $events = $this->smashgg->getTournamentEvents($this->tournament->getSmashggSlug(), true);
        $events = array_filter($events, function ($event) use ($eventIds) {
            return in_array($event['id'], $eventIds);
        });

        foreach ($events as $eventData) {
            $eventId = $eventData['id'];
            $game = $this->findGame($eventData['videogameId']);

            $event = new Event();
            $event->setSmashggId($eventId);
            $event->setTournament($this->tournament);
            $event->setName($eventData['name']);
            $event->setDescription($eventData['description']);
            $event->setGame($game);

            $this->entityManager->persist($event);
            $this->events[$eventId] = $event;
        }
    }

    /**
     * @return void
     */
    protected function processPhases()
    {
        $phases = $this->smashgg->getTournamentPhases($this->tournament->getSmashggSlug());

        foreach ($phases as $phaseData) {
            $phaseId = $phaseData['id'];
            $event = $this->findEvent($phaseData['eventId']);

            if (!$event instanceof Event) {
                continue;
            }

            $phase = new Phase();
            $phase->setSmashggId($phaseId);
            $phase->setEvent($event);
            $phase->setName($phaseData['name']);
            $phase->setPhaseOrder($phaseData['phaseOrder']);

            $this->entityManager->persist($phase);
            $this->phases[$phaseId] = $phase;
        }
    }

    /**
     * @return void
     */
    protected function processGroups()
    {
        $groups = $this->smashgg->getTournamentGroups($this->tournament->getSmashggSlug());

        foreach ($groups as $phaseGroupData) {
            $phaseGroupId = $phaseGroupData['id'];
            $phase = $this->findPhase($phaseGroupData['phaseId']);

            if (!$phase instanceof Phase) {
                continue;
            }

            $phaseGroup = new PhaseGroup();
            $phaseGroup->setSmashggId($phaseGroupId);
            $phaseGroup->setPhase($phase);
            $phaseGroup->setName($phaseGroupData['displayIdentifier']);
            $phaseGroup->setType($phaseGroupData['groupTypeId']);

            $this->entityManager->persist($phaseGroup);

            $this->processPhaseGroupPlayers($phaseGroupId);
            $this->processPhaseGroupEntrants($phaseGroupId);
            $this->processPhaseGroupSets($phaseGroupId, $phaseGroup);
        }
    }

    /**
     * @param int $id The ID of the PhaseGroup.
     */
    protected function processPhaseGroupPlayers(int $id)
    {
        $players = $this->smashgg->getPhaseGroupPlayers($id);

        foreach ($players as $playerData) {
            $playerId = $playerData['id'];

            $player = $this->findPlayer($playerId);
            $player->setGamerTag($playerData['gamerTag']);

            $this->players[$playerId] = $player;
        }

        // We need to flush the entity manager here, otherwise the next event won't find new players created in
        // previous events associated with this tournament.
        $this->entityManager->flush();
    }

    /**
     * @param int $id
     *
     * @TODO Also remove players that are no longer part of the entrant.
     */
    protected function processPhaseGroupEntrants($id)
    {
        $entrants = $this->smashgg->getPhaseGroupEntrants($id);

        foreach ($entrants as $entrantData) {
            $entrantId = $entrantData['id'];

            $entrant = new Entrant();
            $entrant->setSmashggId($entrantId);
            $entrant->setName($entrantData['name']);

            $this->entityManager->persist($entrant);

            foreach ($entrantData['playerIds'] as $playerId) {
                $player = $this->players[$playerId];

                if (!$entrant->hasPlayer($player)) {
                    $entrant->addPlayer($player);
                }
            }

            $this->entrants[$entrantId] = $entrant;
        }
    }

    /**
     * @param int $id
     * @param PhaseGroup $phaseGroup
     */
    protected function processPhaseGroupSets(int $id, PhaseGroup $phaseGroup)
    {
        $sets = $this->smashgg->getPhaseGroupSets($id);

        foreach ($sets as $setData) {
            $setId = $setData['id'];

            $set = new Set();
            $set->setSmashggId($setId);
            $set->setRound($setData['originalRound']);
            $set->setPhaseGroup($phaseGroup);

            $this->entityManager->persist($set);

            $entrantOneId = $setData['entrant1Id'];
            $entrantTwoId = $setData['entrant2Id'];
            $entrantOne = null;
            $entrantTwo = null;

            if ($entrantOneId) {
                $entrantOne = $this->entrants[$entrantOneId];
                $set->setEntrantOne($entrantOne);
            }

            if ($entrantTwoId) {
                $entrantTwo = $this->entrants[$entrantTwoId];
                $set->setEntrantTwo($entrantTwo);
            }

            if ($setData['winnerId'] && $setData['winnerId'] == $setData['entrant1Id']) {
                $set->setWinner($entrantOne);
                $set->setWinnerScore($setData['entrant1Score']);
                $set->setLoser($entrantTwo);
                $set->setLoserScore($setData['entrant2Score']);
            } elseif ($setData['winnerId'] && $setData['winnerId'] == $setData['entrant2Id']) {
                $set->setWinner($entrantTwo);
                $set->setWinnerScore($setData['entrant2Score']);
                $set->setLoser($entrantOne);
                $set->setLoserScore($setData['entrant1Score']);
            }

            if ($set->getLoserScore() === -1) {
                $set->setStatus(Set::STATUS_DQED);
            }
        }
    }

    /**
     * @param int $smashggId
     * @return Game
     */
    protected function findGame(int $smashggId): Game
    {
        if (array_key_exists($smashggId, $this->games)) {
            return $this->games[$smashggId];
        }

        $game = $this->getRepository('CoreBundle:Game')->findOneBy([
            'smashggId' => $smashggId,
        ]);

        if (!$game instanceof Game) {
            $game = new Game();
            $game->setSmashggId($smashggId);

            $this->entityManager->persist($game);
        }

        return $game;
    }

    /**
     * @param int $smashggId
     * @return Event|null
     */
    protected function findEvent(int $smashggId)
    {
        if (array_key_exists($smashggId, $this->events)) {
            return $this->events[$smashggId];
        }

        return null;
    }

    /**
     * @param int $smashggId
     * @return Phase|null
     */
    protected function findPhase(int $smashggId)
    {
        if (array_key_exists($smashggId, $this->phases)) {
            return $this->phases[$smashggId];
        }

        return null;
    }

    /**
     * @param int $smashggId
     * @return Player
     */
    protected function findPlayer(int $smashggId): Player
    {
        $player = $this->getRepository('CoreBundle:Player')->findOneBy([
            'smashggId' => $smashggId,
        ]);

        if (!$player instanceof Player) {
            $player = new Player();
            $player->setSmashggId($smashggId);

            $this->entityManager->persist($player);
        }

        return $player;
    }

    /**
     * @param int $smashggId
     * @return Entrant
     */
    protected function findEntrant(int $smashggId): Entrant
    {
        $entrant = $this->getRepository('CoreBundle:Entrant')->findOneBy([
            'smashggId' => $smashggId,
        ]);

        if (!$entrant instanceof Entrant) {
        }

        return $entrant;
    }
}
