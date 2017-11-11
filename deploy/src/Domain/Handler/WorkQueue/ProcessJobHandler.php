<?php

declare(strict_types = 1);

namespace Domain\Handler\WorkQueue;

use CoreBundle\Entity\Job;
use CoreBundle\Entity\Tournament;
use CoreBundle\Importer\Challonge\Importer as ChallongeImporter;
use CoreBundle\Importer\Smashgg\Importer as SmashggImporter;
use CoreBundle\Service\Smashgg\Smashgg;
use Domain\Command\Event\GenerateResultsCommand;
use Domain\Command\WorkQueue\AddJobCommand;
use Domain\Command\WorkQueue\ProcessJobCommand;
use Domain\Handler\AbstractHandler;
use League\Tactician\CommandBus;
use Reflex\Challonge\Challonge;

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
     * @var Challonge
     */
    protected $challonge;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @param Smashgg $smashgg
     */
    public function setSmashgg(Smashgg $smashgg)
    {
        $this->smashgg = $smashgg;
    }

    /**
     * @param Challonge $challonge
     */
    public function setChallonge(Challonge $challonge)
    {
        $this->challonge = $challonge;
    }

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
        $this->setIo($command->getIo());
        $jobId = $command->getJob()->getId();

        try {
            $this->updateStatus($jobId, Job::STATUS_PROCESSING);
            $data = \GuzzleHttp\json_decode($command->getJob()->getData(), true);

            if (!array_key_exists('type', $data)) {
                throw new \InvalidArgumentException("Job #{$jobId} does not have a 'type' property.");
            }

            switch ($data['type']) {
                case AddJobCommand::TYPE_IMPORT_TOURNAMENT:
                    $message = $this->importTournament($jobId, $data);
                    break;

                case AddJobCommand::TYPE_GENERATE_RESULTS:
                    $message = $this->generateResults($jobId, $data);
                    break;

                default:
                    $message = sprintf("Job #%s does not have a valid 'type' property ('%s' given).", $jobId, $data['type']);
                    throw new \InvalidArgumentException($message);
                    break;
            }

            $this->updateStatus($jobId, Job::STATUS_FINISHED);
            $this->io->success($message);
        } catch (\Exception $e) {
            $this->updateStatus($jobId, Job::STATUS_FAILED);
            $this->io->error($e->getMessage());
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
     * @param int   $jobId
     * @param array $data
     * @return string
     */
    protected function importTournament($jobId, array $data)
    {
        if (!array_key_exists('source', $data)) {
            throw new \InvalidArgumentException("Job #{$jobId} does not have a 'source' property.");
        }

        if ($data['source'] === Tournament::SOURCE_SMASHGG) {
            $importer = new SmashggImporter($this->io, $this->entityManager, $this->smashgg);
            $importer->import($data['smashggId'], $data['events']);
        } elseif ($data['source'] === Tournament::SOURCE_CHALLONGE) {
            $importer = new ChallongeImporter($this->io, $this->entityManager, $this->challonge);
            $importer->import($data['slug']);
        } else {
            throw new \InvalidArgumentException("Unfortunately the source #{$data['source']} can not be handled yet.");
        }

        return 'The tournament was successfully imported.';
    }

    /**
     * @param int   $jobId
     * @param array $data
     * @return string
     */
    protected function generateResults($jobId, array $data)
    {
        if (!array_key_exists('eventId', $data)) {
            throw new \InvalidArgumentException("Job #{$jobId} does not have an 'eventId' property.");
        }

        $command = new GenerateResultsCommand($data['eventId'], $this->io);
        $this->commandBus->handle($command);

        return 'The results were successfully generated.';
    }
}
