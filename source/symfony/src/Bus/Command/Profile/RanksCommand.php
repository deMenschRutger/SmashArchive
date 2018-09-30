<?php

declare(strict_types = 1);

namespace App\Bus\Command\Profile;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class RanksCommand
{
    /**
     * @var string
     */
    private $profileSlug;

    /**
     * @var int|null
     */
    private $eventId;

    /**
     * @param string   $slug
     * @param int|null $eventId
     */
    public function __construct(string $slug, ?int $eventId = null)
    {
        $this->profileSlug = $slug;
        $this->eventId = $eventId;
    }

    /**
     * @return string
     */
    public function getProfileSlug(): string
    {
        return $this->profileSlug;
    }

    /**
     * @return int|null
     */
    public function getEventId(): ?int
    {
        return $this->eventId;
    }
}
