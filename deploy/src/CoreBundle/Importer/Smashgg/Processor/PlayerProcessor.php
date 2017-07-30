<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Smashgg\Processor;

use CoreBundle\Entity\Country;
use CoreBundle\Entity\Player;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PlayerProcessor extends AbstractProcessor
{
    /**
     * @var Player[]
     */
    protected $players = [];

    /**
     * @param int $playerId
     * @return bool
     */
    public function hasPlayer($playerId)
    {
        return array_key_exists($playerId, $this->players);
    }

    /**
     * @param int $playerId
     * @return Player
     */
    public function findPlayer($playerId)
    {
        if ($this->hasPlayer($playerId)) {
            return $this->players[$playerId];
        }

        return null;
    }

    /**
     * @param array   $playerData
     * @param Country $country
     */
    public function processNew(array $playerData, Country $country)
    {
        $playerId = $playerData['id'];

        if ($this->hasPlayer($playerId)) {
            return;
        }

        $player = $this->entityManager->getRepository('CoreBundle:Player')->findOneBy([
            'smashggId' => $playerId,
        ]);

        if (!$player instanceof Player) {
            $player = new Player();
            $player->setSmashggId($playerId);
            $player->setGamerTag($playerData['gamerTag']);

            $this->entityManager->persist($player);
        }

        if ($player->getRegion() === null && $playerData['region']) {
            $player->setRegion($playerData['region']);
        }

        if (!$player->getCountry() instanceof Country && $playerData['country']) {
            $player->setCountry($country);
        }

        $this->players[$playerId] = $player;
    }
}
