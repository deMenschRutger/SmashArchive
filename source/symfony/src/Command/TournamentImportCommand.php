<?php

declare(strict_types = 1);

namespace App\Command;

use App\Bus\Command\Tournament\ImportCommand;
use App\Entity\Tournament;
use App\Service\Smashgg\Smashgg;
use League\Tactician\CommandBus;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
final class TournamentImportCommand extends ContainerAwareCommand
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var Smashgg
     */
    protected $smashgg;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @param LoggerInterface $logger
     * @param CommandBus      $commandBus
     * @param Smashgg         $smashgg
     */
    public function __construct(LoggerInterface $logger, CommandBus $commandBus, Smashgg $smashgg)
    {
        $this->logger = $logger;
        $this->commandBus = $commandBus;
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
            ->setDescription('Import a tournament from a third-party provider')
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);

        $question = new ChoiceQuestion(
            'Which provider would you like to use?',
            [ Tournament::SOURCE_SMASHGG, Tournament::SOURCE_CHALLONGE ]
        );
        $provider = $this->io->askQuestion($question);

        if ($provider === Tournament::SOURCE_SMASHGG) {
            $this->executeSmashgg();
        } elseif ($provider === Tournament::SOURCE_CHALLONGE) {
                $this->executeChallonge();
        } else {
            $this->io->error('Unfortunately that provider is currently not supported.');

            return;
        }

        $this->io->newLine();
        $this->io->success('The tournament was successfully imported.');
    }

    /**
     * @return void
     */
    protected function executeSmashgg()
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

        $command = new ImportCommand(Tournament::SOURCE_SMASHGG, $slug, $selectedEvents);

        $this->commandBus->handle($command);
    }

    /**
     * @return void
     */
    protected function executeChallonge()
    {
        $slug = $this->io->ask('Please enter the slug of this tournament');

        $command = new ImportCommand(Tournament::SOURCE_CHALLONGE, $slug);

        $this->commandBus->handle($command);
    }
}
