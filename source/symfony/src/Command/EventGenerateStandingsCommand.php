<?php

declare(strict_types = 1);

namespace App\Command;

use App\Bus\Command\Event\GenerateStandingsCommand;
use Doctrine\ORM\EntityManagerInterface;
use League\Tactician\CommandBus;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EventGenerateStandingsCommand extends ContainerAwareCommand
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param CommandBus             $commandBus
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(CommandBus $commandBus, EntityManagerInterface $entityManager)
    {
        $this->commandBus = $commandBus;
        $this->entityManager = $entityManager;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('app:event:standings:generate')
            ->setDescription('Generate the standings for an event.')
            ->addOption(
                'event-id',
                'i', // -e is already taken by Symfony.
                InputArgument::OPTIONAL,
                'The ID of the event you wish to generate standings for.'
            )->addOption(
                'all',
                'a',
                InputArgument::OPTIONAL,
                'Pass true if you want standings to be (re)generated for all events.'
            )
        ;
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $eventId = intval($input->getOption('event-id'));
        $all = boolval($input->getOption('all'));

        if ($eventId > 0) {
            $command = new GenerateStandingsCommand($eventId, $io);
            $this->commandBus->handle($command);
        } elseif ($all) {
            $confirmed = $io->confirm(
                'Regenerating all standings could take a long time. Are you sure you wish to continue?',
                false
            );

            if (!$confirmed) {
                $io->warning('The standings generation was aborted.');

                return;
            }

            $eventIds = $this->entityManager->getRepository('App:Event')->getAllEventIds();

            $io->progressStart(count($eventIds));

            foreach ($eventIds as $eventId) {
                $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);

                $command = new GenerateStandingsCommand($eventId, $io);
                $this->commandBus->handle($command);

                $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
                $io->progressAdvance();
            }

            $io->progressFinish();
        } else {
            throw new \InvalidArgumentException("You need to specify either an event ID or the 'all' flag.");
        }

        $io->success('The standings were successfully generated!');
    }
}
