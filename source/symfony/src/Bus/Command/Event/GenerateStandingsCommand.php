<?php

declare(strict_types = 1);

namespace App\Bus\Command\Event;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class GenerateStandingsCommand
{
    /**
     * @var int
     */
    private $eventId;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @param int               $eventId
     * @param SymfonyStyle|null $io
     */
    public function __construct(int $eventId, ?SymfonyStyle $io = null)
    {
        $this->eventId = $eventId;
        $this->io = $io;
    }

    /**
     * @return int
     */
    public function getEventId(): int
    {
        return $this->eventId;
    }

    /**
     * @return SymfonyStyle
     */
    public function getIo(): ?SymfonyStyle
    {
        return $this->io;
    }
}
