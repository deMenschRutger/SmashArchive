<?php

declare(strict_types = 1);

namespace Domain\Command\Player;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class HeadToHeadCommand
{
    /**
     * @var string
     */
    private $playerOneSlug;

    /**
     * @var string
     */
    private $playerTwoSlug;

    /**
     * @param string $playerOneSlug
     * @param string $playerTwoSlug
     */
    public function __construct(string $playerOneSlug, string $playerTwoSlug)
    {
        $this->playerOneSlug = $playerOneSlug;
        $this->playerTwoSlug = $playerTwoSlug;
    }

    /**
     * @return string
     */
    public function getPlayerOneSlug(): string
    {
        return $this->playerOneSlug;
    }

    /**
     * @return string
     */
    public function getPlayerTwoSlug(): string
    {
        return $this->playerTwoSlug;
    }
}
