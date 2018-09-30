<?php

declare(strict_types = 1);

namespace App\Bus\Command\Profile;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class HeadToHeadCommand
{
    /**
     * @var string
     */
    private $profileOneSlug;

    /**
     * @var string
     */
    private $profileTwoSlug;

    /**
     * @param string $playerOneSlug
     * @param string $playerTwoSlug
     */
    public function __construct(string $playerOneSlug, string $playerTwoSlug)
    {
        $this->profileOneSlug = $playerOneSlug;
        $this->profileTwoSlug = $playerTwoSlug;
    }

    /**
     * @return string
     */
    public function getProfileOneSlug(): string
    {
        return $this->profileOneSlug;
    }

    /**
     * @return string
     */
    public function getProfileTwoSlug(): string
    {
        return $this->profileTwoSlug;
    }
}
