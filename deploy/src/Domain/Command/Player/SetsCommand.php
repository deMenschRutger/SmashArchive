<?php

declare(strict_types = 1);

namespace Domain\Command\Player;

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
     * @param int    $page
     * @param int    $limit
     */
    public function __construct(string $slug, $page = self::DEFAULT_PAGE, $limit = self::DEFAULT_LIMIT)
    {
        $this->slug = $slug;
        $this->page = $page ? intval($page) : self::DEFAULT_PAGE;
        $this->limit = $limit ? intval($limit) : self::DEFAULT_LIMIT;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
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
