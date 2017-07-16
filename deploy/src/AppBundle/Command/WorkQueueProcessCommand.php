<?php

declare(strict_types = 1);

namespace AppBundle\Command;

use CoreBundle\Entity\Job as JobEntity;
use Doctrine\ORM\EntityManager;
use Domain\Command\Tournament\Import\SmashggCommand;
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
     *
     * @TODO Automatically clean up old jobs after a job was processed.
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $jobRepository = $this->entityManager->getRepository('CoreBundle:Job');

        $this->pheanstalk->watch('import-tournament');

        while (true) {
            /** @var Job $job */
            $job = $this->pheanstalk->reserve(3600);

            try {
                $data = \GuzzleHttp\json_decode($job->getData(), true);
                $jobId = $job->getId();
                $jobEntity = $jobRepository->findOneBy([ 'queueId' => $jobId ]);

                if ($jobEntity instanceof JobEntity) {
                    $jobEntity->setStatus(JobEntity::STATUS_PROCESSING);
                    $this->entityManager->flush();
                }

                $command = new SmashggCommand($data['smashggId'], $data['events'], true, $this->io);
                $this->commandBus->handle($command);

                $this->io->success('The tournament was successfully imported.');

                // For some reason the job needs to be retrieved a second time here in order to update the status.
                $jobEntity = $jobRepository->findOneBy([ 'queueId' => $jobId ]);

                if ($jobEntity instanceof JobEntity) {
                    $jobEntity->setStatus(JobEntity::STATUS_FINISHED);
                    $this->entityManager->flush();
                }
            } catch (\Exception $e) {
                $this->io->error($e->getMessage());
            } finally {
                $this->pheanstalk->delete($job);
            }
        }
    }
}
