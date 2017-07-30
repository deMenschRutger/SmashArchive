<?php

declare(strict_types = 1);

namespace Domain\Handler\WorkQueue;

use CoreBundle\Entity\Job;
use CoreBundle\Importer\Smashgg\Importer as SmashggImporter;
use CoreBundle\Service\Smashgg\Smashgg;
use Domain\Command\WorkQueue\AddJobCommand;
use Domain\Command\WorkQueue\ProcessJobCommand;
use Domain\Handler\AbstractHandler;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @TODO Automatically clean up old jobs after a job was processed.
 */
class ProcessJobHandler extends AbstractHandler
{
    /**
     * @var Smashgg
     */
    protected $smashgg;

    /**
     * @param Smashgg $smashgg
     */
    public function setSmashgg(Smashgg $smashgg)
    {
        $this->smashgg = $smashgg;
    }

    /**
     * @param ProcessJobCommand $command
     * @return void
     */
    public function handle(ProcessJobCommand $command)
    {
        $io = $command->getIo();
        $jobId = $command->getJob()->getId();

        try {
            $this->updateStatus($jobId, Job::STATUS_PROCESSING);
            $data = \GuzzleHttp\json_decode($command->getJob()->getData(), true);

            if (!array_key_exists('type', $data)) {
                throw new \InvalidArgumentException("Job #{$jobId} does not have a 'type' property.");
            }

            switch ($data['type']) {
                case AddJobCommand::TYPE_TOURNAMENT_IMPORT:
                    $this->importTournament($jobId, $data, $io);
                    break;

                default:
                    $message = sprintf("Job #%s does not have a valid 'type' property ('%s' given).", $jobId, $data['type']);
                    throw new \InvalidArgumentException($message);
                    break;
            }

            $this->updateStatus($jobId, Job::STATUS_FINISHED);
            $io->success('The tournament was successfully imported.');
        } catch (\Exception $e) {
            $this->updateStatus($jobId, Job::STATUS_FAILED);
            $io->error($e->getMessage());
        }
    }

    /**
     * @param int    $jobId
     * @param string $status
     * @return Job
     */
    protected function updateStatus($jobId, $status)
    {
        $job = $this->getRepository('CoreBundle:Job')->findOneBy([ 'queueId' => $jobId ]);

        if ($job instanceof Job) {
            $job->setStatus($status);
            $this->entityManager->flush();
        }

        return $job;
    }

    /**
     * @param int          $jobId
     * @param array        $data
     * @param SymfonyStyle $io
     */
    protected function importTournament($jobId, array $data, SymfonyStyle $io)
    {
        if (!array_key_exists('source', $data)) {
            throw new \InvalidArgumentException("Job #{$jobId} does not have a 'source' property.");
        }

        switch ($data['type']) {
            case AddJobCommand::TYPE_TOURNAMENT_IMPORT:
                $importer = new SmashggImporter($io, $this->entityManager, $this->smashgg);
                break;

            default:
                $message = sprintf("Job #%s does not have a valid 'source' property ('%s' given).", $jobId, $data['source']);
                throw new \InvalidArgumentException($message);
                break;
        }

        $importer->import($data['smashggId'], $data['events']);
    }
}
