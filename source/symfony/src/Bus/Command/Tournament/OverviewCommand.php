<?php

declare(strict_types = 1);

namespace App\Bus\Command\Tournament;

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
     * @var string
     *
     * @Assert\Choice({"name", "dateStart"})
     */
    private $sort;

    /**
     * @var string
     *
     * @Assert\Choice({"asc", "desc"})
     */
    private $order;

    /**
     * @param string $name
     * @param string $location
     * @param int    $page
     * @param int    $limit
     * @param string $sort
     * @param string $order
g    */
    public function __construct(
        ?string $name = null,
        ?string $location = null,
        ?int $page = null,
        ?int $limit = null,
        string $sort = 'name',
        string $order = 'asc'
    ) {
        $this->name = $name;
        $this->location = $location;
        $this->page = $page ? $page : self::DEFAULT_PAGE;
        $this->limit = $limit ? $limit : self::DEFAULT_LIMIT;
        $this->sort = $sort;
        $this->order = $order;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string|null
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

    /**
     * @return string
     */
    public function getSort(): string
    {
        return $this->sort;
    }

    /**
     * @return string
     */
    public function getOrder(): string
    {
        return $this->order;
    }
}
