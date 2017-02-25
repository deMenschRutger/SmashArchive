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
     * @param string $slug
     */
    public function __construct($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getSlug()
    {
        return $this->slug;
    }
}
