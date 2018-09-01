<?php

declare(strict_types = 1);

namespace App\Importer\Smashgg\Processor;

use App\Entity\Game;
use App\Importer\AbstractProcessor;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class GameProcessor extends AbstractProcessor
{
    /**
     * @var Game[]
     */
    protected $games = [];

    /**
     * @param int $gameId
     * @return bool
     */
    public function hasGame($gameId)
    {
        return array_key_exists($gameId, $this->games);
    }

    /**
     * @param int $gameId
     * @return Game
     */
    public function findGame($gameId)
    {
        if ($this->hasGame($gameId)) {
            return $this->games[$gameId];
        }

        return null;
    }

    /**
     * @param array $gameData
     */
    public function processNew(array $gameData)
    {
        $gameId = $gameData['id'];

        if ($this->hasGame($gameId)) {
            return;
        }

        $game = $this->entityManager->getRepository('App:Game')->findOneBy([
            'smashggId' => $gameId,
        ]);

        if (!$game instanceof Game) {
            $game = new Game();
            $game->setSmashggId($gameId);

            $this->entityManager->persist($game);
        }

        $game->setName($gameData['name']);
        $game->setDisplayName($gameData['displayName']);

        $this->games[$gameId] = $game;
    }
}
