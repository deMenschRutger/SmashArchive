<?php

declare(strict_types = 1);

namespace Domain\Command\Tournament\Import;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SmashggCommand
{
    /**
     * @var string
     */
    private $slug;

    /**
     * @var array
     */
    private $eventIds;

    /**
     * @var bool
     */
    private $force;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @param string       $slug
     * @param array        $eventIds
     * @param bool         $force
     * @param SymfonyStyle $io
     */
    public function __construct($slug, $eventIds, $force, $io = null)
    {
        $this->slug = $slug;
        $this->eventIds = $eventIds;
        $this->force = $force;
        $this->io = $io;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return array
     */
    public function getEventIds(): array
    {
        return $this->eventIds;
    }

    /**
     * @return bool
     */
    public function getForce(): bool
    {
        return $this->force;
    }

    /**
     * @return SymfonyStyle
     */
    public function getIo()
    {
        return $this->io;
    }
}
