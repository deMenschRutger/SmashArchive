<?php

declare(strict_types=1);

namespace Domain\Command\Tournament;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsCommand
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
        $this->eventId = intval($eventId);
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return $this->eventId;
    }
}
