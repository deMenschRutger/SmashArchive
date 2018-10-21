<?php

declare(strict_types = 1);

namespace App\Command;

use League\Tactician\CommandBus;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\SemaphoreStore;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class WorkQueueProcessCommand extends ContainerAwareCommand
{
    const JOB_LIMIT = 10;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @var Pheanstalk
     */
    protected $pheanstalk;

    /**
     * @param LoggerInterface $logger
     * @param CommandBus      $commandBus
     * @param Pheanstalk      $pheanstalk
     */
    public function __construct(LoggerInterface $logger, CommandBus $commandBus, Pheanstalk $pheanstalk)
    {
        $this->logger = $logger;
        $this->commandBus = $commandBus;
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
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $store = new SemaphoreStore();
        $factory = new Factory($store);
        $lock = $factory->createLock('app:work-queue:process');

        if ($lock->acquire()) {
            $this->logger->info('Lock acquired.');
        } else {
            $this->logger->warning('Could not acquire a lock.');

            return;
        }

        $counter = 0;

        while ($counter <= self::JOB_LIMIT) {
            /** @var Job $job */
            $job = $this->pheanstalk->reserve(0);

            if (!$job instanceof Job) {
                break;
            }

            try {
                $this->logger->notice("Processing job #{$job->getId()}...");


                // TODO Handle the job.
                var_dump($job);


                $this->logger->notice("Job #{$job->getId()} was successfully processed.");
            } finally {
                $this->pheanstalk->delete($job);
                $counter++;
            }
        }

        $this->logger->info('Releasing the lock...');

        $lock->release();

        $this->logger->info('The lock was released.');
    }
}
