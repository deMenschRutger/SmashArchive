<?php

declare(strict_types = 1);

namespace Domain\Command\Event;

use Symfony\Component\Console\Style\SymfonyStyle;

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
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @param int          $eventId
     * @param SymfonyStyle $io
     */
    public function __construct($eventId, $io = null)
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
    public function getIo()
    {
        return $this->io;
    }
}
