<?php

declare(strict_types = 1);

namespace Domain\Command\WorkQueue;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ProcessJobCommand
{
    /**
     * @var int
     */
    private $queueId;

    /**
     * @param int $queueID
     */
    public function __construct($queueID)
    {
        $this->queueId = $queueID;
    }

    /**
     * @return int
     */
    public function getQueueId(): int
    {
        return $this->queueId;
    }
}
