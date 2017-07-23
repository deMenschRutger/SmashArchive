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

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class WorkQueueProcessCommand extends ContainerAwareCommand
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
        $this->pheanstalk->watch('import-tournament');

        while (true) {
            /** @var Job $job */
            $job = $this->pheanstalk->reserve(3600);

            $command = new ProcessJobCommand($job, $this->io);
            $this->commandBus->handle($command);

            $this->pheanstalk->delete($job);
        }
    }
}
