<?php

declare(strict_types = 1);

namespace Domain\Command\Tournament;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsCommand
{
    /**
     * @var int
     */
    private $tournamentId;

    /**
     * @var int
     */
    private $eventId;

    /**
     * @param int $tournamentId
     * @param int $eventId
     */
    public function __construct($tournamentId = null, $eventId = null)
    {
        $this->tournamentId = $tournamentId === null ? null : intval($tournamentId);
        $this->eventId = $eventId === null ? null : intval($eventId);
    }

    /**
     * @return int
     */
    public function getTournamentId()
    {
        return $this->tournamentId;
    }

    /**
     * @return int
     */
    public function getEventId()
    {
        return $this->eventId;
    }
}
