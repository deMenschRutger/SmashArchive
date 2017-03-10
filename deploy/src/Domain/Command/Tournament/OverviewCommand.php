<?php

declare(strict_types=1);

namespace Domain\Command\Tournament;

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
    private $name;

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
     * @param string $name
     * @param int    $page
     * @param int    $limit
     *
     * @TODO Improve the validation for parameters with default values.
     */
    public function __construct(
        $name = null,
        $page = self::DEFAULT_PAGE,
        $limit = self::DEFAULT_LIMIT
    ) {
        $this->name = $name;
        $this->page = $page ? intval($page) : self::DEFAULT_PAGE;
        $this->limit = $limit ? intval($limit) : self::DEFAULT_LIMIT;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
