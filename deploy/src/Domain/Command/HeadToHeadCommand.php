<?php

declare(strict_types=1);

namespace Domain\Command;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Rutger Mensch <rutger@mediamonks.com>
 */
class HeadToHeadCommand
{
    /**
     * @var int
     *
     * @Assert\Range(min=1)
     */
    private $playerOneId;

    /**
     * @var int
     *
     * @Assert\Range(min=1)
     */
    private $playerTwoId;

    /**
     * @param mixed $playerOneId
     * @param mixed $playerTwoId
     */
    public function __construct($playerOneId, $playerTwoId)
    {
        $this->playerOneId = intval($playerOneId);
        $this->playerTwoId = intval($playerTwoId);
    }

    /**
     * @return int
     */
    public function getPlayerOneId(): int
    {
        return $this->playerOneId;
    }

    /**
     * @return int
     */
    public function getPlayerTwoId(): int
    {
        return $this->playerTwoId;
    }
}
