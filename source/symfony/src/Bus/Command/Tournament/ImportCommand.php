<?php

declare(strict_types = 1);

namespace App\Bus\Command\Tournament;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ImportCommand
{
    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var array
     */
    private $events;

    /**
     * @param string     $source
     * @param string     $slug
     * @param array|null $events
     */
    public function __construct(string $source, string $slug, ?array $events = null)
    {
        $this->source = $source;
        $this->slug = $slug;
        $this->events = $events;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return array|null
     */
    public function getEvents(): ?array
    {
        return $this->events;
    }
}
