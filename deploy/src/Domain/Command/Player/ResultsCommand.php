<?php

declare(strict_types=1);

namespace Domain\Command\Player;

use Symfony\Component\Validator\Constraints as Assert;

/**
 * @author Rutger Mensch <rutger@mediamonks.com>
 */
class ResultsCommand
{
    /**
     * @var int
     *
     * @Assert\Range(min=1)
     */
    private $playerId;

    /**
     * @param mixed $playerId
     */
    public function __construct($playerId)
    {
        $this->playerId = intval($playerId);
    }

    /**
     * @return int
     */
    public function getPlayerId(): int
    {
        return $this->playerId;
    }
}
