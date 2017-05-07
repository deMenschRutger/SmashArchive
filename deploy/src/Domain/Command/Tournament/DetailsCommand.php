<?php

declare(strict_types=1);

namespace Domain\Command\Tournament;

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
     * @var bool
     */
    private $includeResults;

    /**
     * @param string $slug
     * @param bool   $includeResults
     */
    public function __construct($slug, $includeResults = false)
    {
        $this->slug = $slug;
        $this->includeResults = (bool) $includeResults;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @return bool
     */
    public function getIncludeResults(): bool
    {
        return $this->includeResults;
    }
}
