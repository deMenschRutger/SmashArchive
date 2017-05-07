<?php

declare(strict_types=1);

namespace Domain\Command\Player;

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
     * @var array
     */
    private $sets;

    /**
     * @param string $slug
     * @param array  $sets
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
     * @return array
     */
    public function getSets()
    {
        return $this->sets;
    }
}
