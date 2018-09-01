<?php

declare(strict_types = 1);

namespace App\Command;

use App\Bus\Command\Event\GenerateRanksCommand;
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
class EventGenerateRanksCommand extends ContainerAwareCommand
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
            ->setName('app:event:rankings:generate')
            ->setDescription('Generate the rankings for an event.')
            ->addOption(
                'event-id',
                'i',
                InputArgument::OPTIONAL,
                'The ID of the event you wish to generate rankings for.'
            )->addOption(
                'all',
                'a',
                InputArgument::OPTIONAL,
                'Pass true if you want ranks to be (re)generated for all events.'
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
        $io = new SymfonyStyle($input, $output);

        $eventId = intval($input->getOption('event-id'));
        $all = boolval($input->getOption('all'));

        if ($eventId > 0) {
            $command = new GenerateRanksCommand($eventId, $io);
            $this->commandBus->handle($command);
        } elseif ($all) {
            $confirmed = $io->confirm(
                'Regenerating all rankings could take a long time. Are you sure you wish to continue?',
                false
            );

            if (!$confirmed) {
                $io->warning('The rankings generation was aborted.');

                return;
            }

            $eventIds = $this->entityManager->getRepository('CoreBundle:Event')->getAllEventIds();

            $io->progressStart(count($eventIds));

            foreach ($eventIds as $eventId) {
                $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);

                $command = new GenerateRanksCommand($eventId, $io);
                $this->commandBus->handle($command);

                $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
                $io->progressAdvance();
            }

            $io->progressFinish();
        } else {
            throw new \InvalidArgumentException("You need to specify either an event ID or the 'all' flag.");
        }

        $io->success('The rankings were successfully generated!');
    }
}
