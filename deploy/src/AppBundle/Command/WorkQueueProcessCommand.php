<?php

declare(strict_types = 1);

namespace AppBundle\Command;

use Doctrine\ORM\EntityManager;
use Domain\Command\WorkQueue\ProcessJobCommand;
use League\Tactician\CommandBus;
use Leezy\PheanstalkBundle\Proxy\PheanstalkProxy;
use Pheanstalk\Job;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Filesystem\LockHandler;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class WorkQueueProcessCommand extends ContainerAwareCommand
{
    const JOB_LIMIT = 10;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var PheanstalkProxy
     */
    protected $pheanstalk;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @param CommandBus      $commandBus
     * @param EntityManager   $entityManager
     * @param PheanstalkProxy $pheanstalk
     */
    public function __construct(CommandBus $commandBus, EntityManager $entityManager, PheanstalkProxy $pheanstalk)
    {
        $this->commandBus = $commandBus;
        $this->entityManager = $entityManager;
        $this->pheanstalk = $pheanstalk;

        parent::__construct();
    }

    /**
     * @return void
     */
    protected function configure()
    {
        $this
            ->setName('app:work-queue:process')
            ->setDescription('Process jobs that were added to the work queue')
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
        $lockHandler = new LockHandler('app:work-queue:process');

        if ($lockHandler->lock()) {
            $this->io->comment('Lock acquired.');
        } else {
            $this->io->warning('Could not acquire a lock.');

            return;
        }

        $this->pheanstalk->watch('import-tournament')->watch('generate-results');

        $counter = 0;

        while ($counter < self::JOB_LIMIT) {
            /** @var Job $job */
            $job = $this->pheanstalk->reserve(0);

            if (!$job instanceof Job) {
                break;
            }

            try {
                $command = new ProcessJobCommand($job, $this->io);
                $this->commandBus->handle($command);
            } finally {
                $this->pheanstalk->delete($job);
                $counter++;
            }
        }

        $this->io->comment('Releasing lock...');

        $lockHandler->release();
    }
}
