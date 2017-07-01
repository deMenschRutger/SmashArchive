<?php

declare(strict_types = 1);

namespace Domain\Command\Player;

use Knp\Component\Pager\Pagination\PaginationInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsCommand
{
    /**
     * @var string
     */
    private $slug;

    /**
     * @var PaginationInterface
     */
    private $sets;

    /**
     * @param string              $slug
     * @param PaginationInterface $sets
     */
    public function __construct($slug, $sets = null)
    {
        $this->slug = $slug;
        $this->sets = $sets;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return PaginationInterface
     */
    public function getSets()
    {
        return $this->sets;
    }
}
