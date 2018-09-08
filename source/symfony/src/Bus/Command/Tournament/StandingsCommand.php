<?php

declare(strict_types = 1);

namespace App\Bus\Command\Tournament;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class StandingsCommand
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
    public function __construct(?int $tournamentId = null, ?int $eventId = null)
    {
        $this->tournamentId = $tournamentId;
        $this->eventId = $eventId;
    }

    /**
     * @return int|null
     */
    public function getTournamentId(): ?int
    {
        return $this->tournamentId;
    }

    /**
     * @return int|null
     */
    public function getEventId(): ?int
    {
        return $this->eventId;
    }
}
