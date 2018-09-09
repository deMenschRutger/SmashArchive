<?php

declare(strict_types = 1);

namespace App\Importer\Smashgg\Processor;

use App\Entity\Country;
use App\Entity\Player;
use App\Entity\Profile;
use App\Entity\Tournament;
use App\Importer\AbstractProcessor;

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
     *
     * @return bool
     */
    public function hasPlayer($playerId)
    {
        return array_key_exists($playerId, $this->players);
    }

    /**
     * @param int $playerId
     *
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
     * @param array      $playerData
     * @param Country    $country
     * @param Tournament $originTournament
     */
    public function processNew(array $playerData, Country $country = null, Tournament $originTournament = null)
    {
        $playerId = $playerData['id'];

        if ($this->hasPlayer($playerId)) {
            return;
        }

        $player = $this->entityManager->getRepository('App:Player')->findOneBy([
            'externalId' => $playerId,
            'type'       => Player::SOURCE_SMASHGG,
        ]);

        if (!$player instanceof Player) {
            $player = new Player();
            $player->setName($playerData['gamerTag']);
            $player->setOriginTournament($originTournament);
            $player->setType(Player::SOURCE_SMASHGG);
            $player->setExternalId(strval($playerId));

            $this->entityManager->persist($player);
        }

        $profile = $player->getProfile();

        if ($profile instanceof Profile) {
            if ($profile->getRegion() === null && $playerData['region']) {
                $profile->setRegion($playerData['region']);
            }

            if (!$profile->getCountry() instanceof Country && $playerData['country']) {
                $profile->setCountry($country);
            }
        }

        $this->players[$playerId] = $player;
    }
}
