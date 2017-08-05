<?php

declare(strict_types = 1);

namespace AppBundle\Command;

use Doctrine\ORM\EntityManager;
use Domain\Command\Event\GenerateResultsCommand;
use League\Tactician\CommandBus;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EventGenerateResultsCommand extends ContainerAwareCommand
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param CommandBus    $commandBus
     * @param EntityManager $entityManager
     */
    public function __construct(CommandBus $commandBus, EntityManager $entityManager)
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
            ->setName('app:event:generate:results')
            ->setDescription('Generate the complete results for an entire event.')
            ->addOption(
                'event-id',
                'i',
                InputArgument::OPTIONAL,
                'The ID of the event you wish to generate results for.'
            )->addOption(
                'all',
                'a',
                InputArgument::OPTIONAL,
                'Pass true if you want results to be (re)generated for all events.'
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
            $command = new GenerateResultsCommand($eventId, $io);
            $this->commandBus->handle($command);
        } elseif ($all) {
            $confirmed = $io->confirm(
                'Regenerating all results could take a long time. Are you sure you wish to continue?',
                false
            );

            if (!$confirmed) {
                $io->warning('The results generation was aborted.');

                return;
            }

            $eventIds = $this->entityManager->getRepository('CoreBundle:Event')->getAllEventIds();

            $io->progressStart(count($eventIds));

            foreach ($eventIds as $eventId) {
                $output->setVerbosity(OutputInterface::VERBOSITY_QUIET);

                $command = new GenerateResultsCommand($eventId, $io);
                $this->commandBus->handle($command);

                $output->setVerbosity(OutputInterface::VERBOSITY_NORMAL);
                $io->progressAdvance();
            }

            $io->progressFinish();
        } else {
            throw new \InvalidArgumentException("You need to specify either an event ID or the 'all' flag.");
        }

        $io->success('The results were successfully generated!');
    }
}
