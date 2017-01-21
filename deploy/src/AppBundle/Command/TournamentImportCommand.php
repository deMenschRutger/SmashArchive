<?php

declare(strict_types=1);

namespace AppBundle\Command;

use CoreBundle\Entity\Event;
use CoreBundle\Entity\Game;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Tournament;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use GuzzleHttp\Client;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch (rutger@rutgermensch.com)
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
            ->setName('tournament:import')
            ->setDescription('Import a tournament from a third-party (like smash.gg)')
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
        $slug = 'tournament/syndicate-2016';
        $response = $client->get('https://api.smash.gg/'.$slug, [
            'query' => [
                'expand' => [
                    'event',
                    'phase',
                    'groups',
                ],
            ],
        ]);

        $apiData = \GuzzleHttp\json_decode($response->getBody(), true);
        $tournamentData = $apiData['entities']['tournament'];
        $eventsData = $apiData['entities']['event'];

        $events = [];
        $games = [];
        $phases = [];
        $phaseGroups = [];

        $tournament = $this->findTournament($slug);
        $tournament->setName($tournamentData['name']);
        $tournament->setSlug($tournamentData['shortSlug']); // TODO Create slug ourselves.

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

        foreach ($apiData['entities']['groups'] as $phaseGroupData) {
            $phaseGroupId = $phaseGroupData['id'];
            $phaseGroup = $this->findPhaseGroup($phaseGroupId);
            $phaseGroup->setName($phaseGroupData['displayIdentifier']);

            $phase = $phases[$phaseGroupData['phaseId']];
            $phaseGroup->setPhase($phase);

            $this->processPhaseGroup($phaseGroupId);

            $phaseGroups[$phaseGroupId] = $phaseGroup;
        }

        $this->entityManager->flush();
        $this->io->success('Successfully imported the tournament!');
    }

    /**
     * @param int $id
     */
    protected function processPhaseGroup(int $id)
    {
        $client = new Client();
        $response = $client->get('https://api.smash.gg/phase_group/'.$id, [
            'query' => [
                'expand' => [
                    'sets',
                ],
            ],
        ]);

        $apiData = \GuzzleHttp\json_decode($response->getBody(), true);
        $phaseGroupData = $apiData;
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
     * @param string $entityName
     * @return EntityRepository
     */
    protected function getRepository(string $entityName): EntityRepository
    {
        return $this->entityManager->getRepository($entityName);
    }
}
