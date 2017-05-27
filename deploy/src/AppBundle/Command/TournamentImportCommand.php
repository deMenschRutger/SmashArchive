<?php

declare(strict_types = 1);

namespace AppBundle\Command;

use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Service\Smashgg\Smashgg;
use Doctrine\ORM\EntityManager;
use Domain\Command\Tournament\Import\SmashggCommand;
use GuzzleHttp\Client;
use League\Tactician\CommandBus;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class TournamentImportCommand extends ContainerAwareCommand
{
    const PROVIDER_SMASHGG = 'smash.gg';
    const PROVIDER_CHALLONGE = 'Challonge';
    const PROVIDER_TIO = 'TIO';

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var Smashgg
     */
    protected $smashgg;

    /**
     * @param CommandBus    $commandBus
     * @param EntityManager $entityManager
     * @param Smashgg       $smashgg
     */
    public function __construct(CommandBus $commandBus, EntityManager $entityManager, Smashgg $smashgg)
    {
        $this->commandBus = $commandBus;
        $this->entityManager = $entityManager;
        $this->smashgg = $smashgg;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('app:tournament:import')
            ->setDescription('Import a tournament from a third-party')
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_OPTIONAL,
                'Whether or not existing data should be overwritten or not.'
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

        $question = new ChoiceQuestion(
            'Which provider would you like to use?',
            [ self::PROVIDER_SMASHGG, self::PROVIDER_CHALLONGE, self::PROVIDER_TIO ]
        );
        $provider = $this->io->askQuestion($question);
        $force = (bool) $input->getOption('force');

        if ($provider === self::PROVIDER_SMASHGG) {
            $this->executeSmashgg($force);
        } else {
            $this->io->error('Unfortunately that provider is currently not supported.');
        }
    }

    /**
     * @param bool $force
     * @return void
     */
    protected function executeSmashgg($force)
    {
        $slug = $this->io->ask('Please enter the slug of this tournament');
        $events = $this->smashgg->getTournamentEvents($slug, true);

        $ids = [];
        $answers = [];

        foreach ($events as $event) {
            $ids[$event['name']] = $event['id'];
            $answers[] = $event['name'];
        }

        $question = new ChoiceQuestion('Please select the IDs of the events you would like to import', $answers);
        $question->setMultiselect(true);
        $selectedEvents = (array) $this->io->askQuestion($question);

        foreach ($selectedEvents as &$selectedEvent) {
            $selectedEvent = $ids[$selectedEvent];
        }

        $command = new SmashggCommand($slug, $selectedEvents, $force);
        $this->commandBus->handle($command);
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
            $set->setRound($setData['originalRound']);
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
}
