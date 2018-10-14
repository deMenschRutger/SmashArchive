<?php

declare(strict_types = 1);

namespace App\Command;

use League\Tactician\CommandBus;
use Pheanstalk\Job;
use Pheanstalk\Pheanstalk;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Lock\Factory;
use Symfony\Component\Lock\Store\SemaphoreStore;

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
     * @var Pheanstalk
     */
    protected $pheanstalk;

    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @param CommandBus $commandBus
     * @param Pheanstalk $pheanstalk
     */
    public function __construct(CommandBus $commandBus, Pheanstalk $pheanstalk)
    {
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
        $this->io = new SymfonyStyle($input, $output);

        // TODO Use a Monolog logger?
        if ($this->io->getVerbosity() <= OutputInterface::VERBOSITY_NORMAL) {
            $this->io->setVerbosity(OutputInterface::VERBOSITY_QUIET);
        }

        $store = new SemaphoreStore();
        $factory = new Factory($store);
        $lock = $factory->createLock('app:work-queue:process');

        if ($lock->acquire()) {
            $this->io->comment('Lock acquired.');
        } else {
            $this->io->warning('Could not acquire a lock.');

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
                // TODO Handle the job.
                var_dump($job);
            } finally {
                $this->pheanstalk->delete($job);
                $counter++;
            }
        }

        $this->io->comment('Releasing the lock...');

        $lock->release();

        $this->io->comment('The lock was released.');
    }
}
