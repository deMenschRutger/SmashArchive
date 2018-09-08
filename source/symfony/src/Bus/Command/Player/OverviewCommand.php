<?php

declare(strict_types = 1);

namespace App\Bus\Command\Player;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class OverviewCommand
{
    const DEFAULT_PAGE = 1;
    const DEFAULT_LIMIT = 50;

    /**
     * @var string
     */
    private $tag;

    /**
     * @var string
     */
    private $location;

    /**
     * @var int
     *
     * @Assert\Range(min=1)
     */
    private $page;

    /**
     * @var int
     *
     * @Assert\Range(min=1, max=250)
     */
    private $limit;

    /**
     * @param string $tag
     * @param string $location
     * @param int    $page
     * @param int    $limit
     */
    public function __construct(
        ?string $tag = null,
        ?string $location = null,
        ?int $page = null,
        ?int $limit = null
    ) {
        $this->tag = $tag;
        $this->location = $location;
        $this->page = $page ? $page : self::DEFAULT_PAGE;
        $this->limit = $limit ? $limit : self::DEFAULT_LIMIT;
    }

    /**
     * @return string
     */
    public function getTag(): ?string
    {
        return $this->tag;
    }

    /**
     * @return string
     */
    public function getLocation(): ?string
    {
        return $this->location;
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
