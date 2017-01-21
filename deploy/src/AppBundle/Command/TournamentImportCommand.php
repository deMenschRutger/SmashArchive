<?php

declare(strict_types=1);

namespace AppBundle\Command;

use CoreBundle\Entity\Event;
use CoreBundle\Entity\Game;
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
        $response = $client->get('https://api.smash.gg/'.$slug.'?expand[]=event');

        $apiData = \GuzzleHttp\json_decode($response->getBody(), true);
        $tournamentData = $apiData['entities']['tournament'];
        $eventsData = $apiData['entities']['event'];
        $games = [];

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
            $event = $this->findEvent($eventData['id'], $tournament);
            $event->setName($eventData['name']);
            $event->setDescription($eventData['description']);
            // TODO Assign game to event.
        }

        $this->entityManager->flush();
        $this->io->success('Successfully imported the tournament!');
    }

    /**
     * @param string $slug
     * @return Tournament|null
     */
    protected function findTournament(string $slug)
    {
        $tournament = $this->getRepository('CoreBundle:Tournament')->findOneBy([
            'slug' => $slug,
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
     * @return Event|null
     */
    protected function findEvent(int $smashGgId, Tournament $tournament)
    {
        // TODO Also search by tournament.
        $event = $this->getRepository('CoreBundle:Event')->findOneBy([
            'smashggId' => $smashGgId,
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
    protected function findGame(int $smashGgId)
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
     * @param string $entityName
     * @return EntityRepository
     */
    protected function getRepository(string $entityName)
    {
        return $this->entityManager->getRepository($entityName);
    }
}
