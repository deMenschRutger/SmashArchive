<?php

declare(strict_types = 1);

namespace Domain\Command\Player;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsCommand
{
    /**
     * @var string
     */
    private $profileSlug;

    /**
     * @var string
     */
    private $eventId;

    /**
     * @param string $slug
     * @param string $eventId
     */
    public function __construct($slug, $eventId = null)
    {
        $this->profileSlug = $slug;
        $this->eventId = $eventId;
    }

    /**
     * @return string
     */
    public function getProfileSlug()
    {
        return $this->profileSlug;
    }

    /**
     * @return string
     */
    public function getEventId()
    {
        return $this->eventId;
    }
}
