<?php

declare(strict_types=1);

namespace Domain\Command\Player;

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
     * @param string $tag
     * @param int    $page
     * @param int    $limit
     */
    public function __construct(
        $tag = null,
        $page = self::DEFAULT_PAGE,
        $limit = self::DEFAULT_LIMIT
    ) {
        $this->tag = $tag;
        $this->page = $page ? intval($page) : self::DEFAULT_PAGE;
        $this->limit = intval($limit);
    }

    /**
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
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
