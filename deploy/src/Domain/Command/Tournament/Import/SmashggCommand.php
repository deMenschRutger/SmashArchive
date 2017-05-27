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
    private $events;

    /**
     * @param string $slug
     * @param array  $events
     */
    public function __construct($slug, $events)
    {
        $this->slug = $slug;
        $this->events = $events;
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
    public function getEvents(): array
    {
        return $this->events;
    }
}
