<?php

declare(strict_types = 1);

namespace AppBundle\Command;

use CoreBundle\Bracket\DoubleEliminationBracket;
use CoreBundle\Bracket\SingleEliminationBracket;
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
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @TODO Refactor this command, it's a mess.
 */
class TournamentImportCommand extends ContainerAwareCommand
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('app:tournament:import')
            ->setDescription('Import a tournament from a third-party (like smash.gg)')
            ->addArgument(
                'slug',
                InputArgument::REQUIRED,
                'The slug of the tournament on smash.gg.'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Import tournament...');

        $client = new Client();
//        $slug = 'tournament/arcamelee-1';
//        $slug = 'tournament/syndicate-2016';
        $slug = $input->getArgument('slug');
        $response = $client->get('https://api.smash.gg/'.$slug, [
            'query' => [
                'expand' => ['event', 'phase', 'groups'],
            ],
        ]);

        $apiData = \GuzzleHttp\json_decode($response->getBody(), true);
        $tournamentData = $apiData['entities']['tournament'];
        $eventsData = $apiData['entities']['event'];

        $events = [];
        $games = [];
        $phases = [];

        $tournament = $this->findTournament($slug);
        $tournament->setName($tournamentData['name']);
        $tournament->setIsActive(true);
        // TODO Check with smash.gg if the tournament is actually complete.
        $tournament->setIsComplete(true);

        $this->io->comment('Starting import...');

        foreach ($apiData['entities']['videogame'] as $gameData) {
            $gameId = $gameData['id'];
            $game = $this->findGame($gameId);
            $game->setName($gameData['name']);
            $game->setDisplayName($gameData['displayName']);

            $games[$gameId] = $game;
        }

        foreach ($eventsData as $eventData) {
            $eventId = $eventData['id'];
            $event = $this->findEvent($eventId, $tournament);
            $event->setName($eventData['name']);
            $event->setDescription($eventData['description']);
            $event->setTournament($tournament);

            $game = $games[$eventData['videogameId']];
            $event->setGame($game);

            $events[$eventId] = $event;
        }

        foreach ($apiData['entities']['phase'] as $phaseData) {
            $phaseId = $phaseData['id'];
            $phase = $this->findPhase($phaseId);
            $phase->setName($phaseData['name']);
            $phase->setPhaseOrder($phaseData['phaseOrder']);

            $event = $events[$phaseData['eventId']];
            $phase->setEvent($event);

            $phases[$phaseId] = $phase;
        }

        $toBeUpdatedPhaseGroups = [];

        foreach ($apiData['entities']['groups'] as $phaseGroupData) {
            $phaseGroupId = $phaseGroupData['id'];
            $phaseGroup = $this->findPhaseGroup($phaseGroupId);
            $phaseGroup->setName($phaseGroupData['displayIdentifier']);
            $phaseGroup->setType($phaseGroupData['groupTypeId']);

            $phase = $phases[$phaseGroupData['phaseId']];
            $phaseGroup->setPhase($phase);

            $this->processPhaseGroup($phaseGroupId, $phaseGroup);
            $toBeUpdatedPhaseGroups[] = $phaseGroup;
        }

        $this->io->writeln('Flushing the entity manager...');
        $this->entityManager->flush();

        foreach ($toBeUpdatedPhaseGroups as $phaseGroup) {
            $this->updatePhaseGroup($phaseGroup);
        }

        $this->entityManager->flush();
        $this->io->success('Successfully imported the tournament!');
    }

    /**
     * @param int $id The ID of the PhaseGroup.
     * @param PhaseGroup $phaseGroup
     */
    protected function processPhaseGroup(int $id, PhaseGroup $phaseGroup)
    {
        $client = new Client();
        $response = $client->get('https://api.smash.gg/phase_group/'.$id, [
            'query' => [
                'expand' => ['sets', 'entrants', 'players'],
            ],
        ]);

        $apiData = \GuzzleHttp\json_decode($response->getBody(), true);
        $entrants = [];
        $players = [];

        foreach ($apiData['entities']['player'] as $playerData) {
            $playerId = $playerData['id'];
            $player = $this->findPlayer($playerId);
            $player->setGamerTag($playerData['gamerTag']);

            $players[$playerId] = $player;
        }

        // We need to flush the entity manager here, otherwise the next event won't find new players created in
        // previous events associated with this tournament.
        $this->entityManager->flush();

        foreach ($apiData['entities']['entrants'] as $entrantData) {
            $entrantId = $entrantData['id'];
            $entrant = $this->findEntrant($entrantId);
            $entrant->setName($entrantData['name']);

            foreach ($entrantData['playerIds'] as $playerId) {
                $player = $players[$playerId];

                if (!$entrant->hasPlayer($player)) {
                    $entrant->addPlayer($player);
                }
            }

            // TODO Also remove players that are no longer part of the entrant.

            $entrants[$entrantId] = $entrant;
        }

        $setCount = count($apiData['entities']['sets']);

        $this->io->comment("Importing sets for phase group #{$id}.");
        $this->io->progressStart($setCount);

        foreach ($apiData['entities']['sets'] as $setData) {
            $set = $this->findSet($setData['id']);
            $set->setRound($setData['round']);
            $set->setPhaseGroup($phaseGroup);

            $entrantOneId = $setData['entrant1Id'];
            $entrantTwoId = $setData['entrant2Id'];
            $entrantOne = null;
            $entrantTwo = null;

            if ($entrantOneId) {
                $entrantOne = $entrants[$entrantOneId];
                $set->setEntrantOne($entrantOne);
            }

            if ($entrantTwoId) {
                $entrantTwo = $entrants[$entrantTwoId];
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
                $set->setIsForfeit(true);
            }

            $this->io->progressAdvance(1);
        }

        $this->io->progressFinish();
    }

    /**
     * @param PhaseGroup $phaseGroup
     */
    protected function updatePhaseGroup($phaseGroup)
    {
        $bracket = null;

        switch ($phaseGroup->getType()) {
            case PhaseGroup::TYPE_SINGLE_ELIMINATION;
                $bracket = new SingleEliminationBracket($phaseGroup);
                break;

            case PhaseGroup::TYPE_DOUBLE_ELIMINATION;
                $bracket = new DoubleEliminationBracket($phaseGroup);
                break;
        }

        if ($bracket) {
            foreach ($phaseGroup->getSets() as $set) {
                $bracket->determineRoundName($set);
                $bracket->determineIsGrandFinals($set);
            }
        }
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
    protected function findSet($smashGgId): Set
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

    /**
     * @param string $entityName
     * @return EntityRepository
     */
    protected function getRepository(string $entityName): EntityRepository
    {
        return $this->entityManager->getRepository($entityName);
    }
}
