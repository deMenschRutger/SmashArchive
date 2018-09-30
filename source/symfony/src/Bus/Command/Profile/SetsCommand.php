<?php

declare(strict_types = 1);

namespace App\Bus\Command\Profile;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SetsCommand
{
    const DEFAULT_PAGE = 1;
    const DEFAULT_LIMIT = 50;

    /**
     * @var string
     */
    private $slug;

    /**
     * @var int
     */
    private $eventId;

    /**
     * @var bool
     */
    private $sortByPhase;

    /**
     * @var int
     *
     * @Assert\Range(min=1)
     */
    private $page;

    /**
     * @var int
     *
     * @Assert\Range(min=1)
     */
    private $limit;

    /**
     * @param string $slug
     * @param int    $eventId
     * @param bool   $sortByPhase
     * @param int    $page
     * @param int    $limit
     */
    public function __construct(
        string $slug,
        ?int $eventId = null,
        bool $sortByPhase = false,
        ?int $page = null,
        ?int $limit = null
    ) {
        $this->slug = $slug;
        $this->eventId = $eventId;
        $this->sortByPhase = $sortByPhase;
        $this->page = $page ? $page : self::DEFAULT_PAGE;
        $this->limit = $limit ? $limit : self::DEFAULT_LIMIT;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return int|null
     */
    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    /**
     * @return bool
     */
    public function getSortByPhase(): bool
    {
        return $this->sortByPhase;
    }

    /**
     * @return int
     */
    public function getPage(): int
    {
        return $this->page;
    }

    /**
     * @return int
     */
    public function getLimit(): int
    {
        return $this->limit;
    }
}
