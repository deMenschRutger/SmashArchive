<?php

declare(strict_types = 1);

namespace Domain\Command\Tournament\Import;

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
     * @param string $slug
     * @param array  $eventIds
     * @param bool   $force
     */
    public function __construct($slug, $eventIds, $force)
    {
        $this->slug = $slug;
        $this->eventIds = $eventIds;
        $this->force = $force;
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
}
