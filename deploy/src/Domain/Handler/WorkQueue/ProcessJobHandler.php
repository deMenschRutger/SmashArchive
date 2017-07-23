<?php

declare(strict_types = 1);

namespace Domain\Handler\WorkQueue;

use CoreBundle\Entity\Job;
use Domain\Command\Tournament\Import\SmashggCommand;
use Domain\Command\WorkQueue\ProcessJobCommand;
use Domain\Handler\AbstractHandler;
use League\Tactician\CommandBus;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @TODO Automatically clean up old jobs after a job was processed.
 */
class ProcessJobHandler extends AbstractHandler
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @param CommandBus $commandBus
     */
    public function setCommandBus(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    /**
     * @param ProcessJobCommand $command
     * @return void
     */
    public function handle(ProcessJobCommand $command)
    {
        $jobId = $command->getJob()->getId();
        $io = $command->getIo();

        try {
            $this->updateStatus($jobId, Job::STATUS_PROCESSING);
            $data = \GuzzleHttp\json_decode($command->getJob()->getData(), true);

            $command = new SmashggCommand($data['smashggId'], $data['events'], true, $command->getIo());
            $this->commandBus->handle($command);

            $this->updateStatus($jobId, Job::STATUS_FINISHED);
            $io->success('The tournament was successfully imported.');
        } catch (\Exception $e) {
            $this->updateStatus($jobId, Job::STATUS_FAILED);
            $io->error($e->getMessage());
        }
    }

    /**
     * For some reason the job needs to be retrieved again each time we update the status.
     *
     * @param int    $id
     * @param string $status
     * @return Job
     */
    protected function updateStatus($id, $status)
    {
        $job = $this->getRepository('CoreBundle:Job')->findOneBy([ 'queueId' => $id ]);

        if ($job instanceof Job) {
            $job->setStatus($status);
            $this->entityManager->flush();
        }

        return $job;
    }
}
