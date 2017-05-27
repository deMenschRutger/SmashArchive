<?php

declare(strict_types = 1);

namespace AppBundle\Command;

use CoreBundle\Service\Smashgg\Smashgg;
use Doctrine\ORM\EntityManager;
use Domain\Command\Tournament\Import\SmashggCommand;
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

            return;
        }

        $this->io->success('The tournament was successfully imported.');
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
}
