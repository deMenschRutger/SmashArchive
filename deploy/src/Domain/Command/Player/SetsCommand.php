<?php

declare(strict_types=1);

namespace Domain\Command\Player;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SetsCommand
{
    /**
     * @var string
     */
    private $slug;

    /**
     * @var string
     */
    private $format;

    /**
     * @param string $slug
     * @param string $format
     */
    public function __construct(string $slug, string $format = 'flat')
    {
        $this->slug = $slug;
        $this->format = $format;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }
}
