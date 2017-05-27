<?php

declare(strict_types = 1);

namespace Domain\Command\Event;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class GenerateResultsCommand
{
    /**
     * @var int
     */
    private $eventId;

    /**
     * @param int $eventId
     */
    public function __construct($eventId)
    {
        $this->eventId = $eventId;
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return $this->eventId;
    }
}
