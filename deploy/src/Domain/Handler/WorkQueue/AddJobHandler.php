<?php

declare(strict_types = 1);

namespace Domain\Handler\WorkQueue;

use CoreBundle\Entity\Job;
use Domain\Command\WorkQueue\AddJobCommand;
use Domain\Handler\AbstractHandler;
use Leezy\PheanstalkBundle\Proxy\PheanstalkProxy;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class AddJobHandler extends AbstractHandler
{
    /**
     * @var PheanstalkProxy
     */
    protected $pheanstalk;

    /**
     * @param PheanstalkProxy $pheanstalk
     */
    public function setPheanstalk(PheanstalkProxy $pheanstalk)
    {
        $this->pheanstalk = $pheanstalk;
    }

    /**
     * @param AddJobCommand $command
     * @return Job
     */
    public function handle(AddJobCommand $command)
    {
        $job = \GuzzleHttp\json_encode($command->getJob());
        $jobId = $this->pheanstalk->useTube($command->getTube())->put($job);

        $jobEntity = new Job();
        $jobEntity->setQueueId($jobId);
        $jobEntity->setName($command->getName());

        $entityManager = $this->getEntityManager();
        $entityManager->persist($jobEntity);
        $entityManager->flush();

        return $jobEntity;
    }
}
