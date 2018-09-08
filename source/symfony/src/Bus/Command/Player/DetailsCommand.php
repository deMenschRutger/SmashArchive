<?php

declare(strict_types = 1);

namespace App\Bus\Command\Player;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class DetailsCommand
{
    /**
     * @var string
     */
    private $slug;

    /**
     * @param string $slug
     */
    public function __construct(string $slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }
}
