<?php

declare(strict_types = 1);

namespace App\Importer\Smashgg\Processor;

use App\Entity\Entrant;
use App\Entity\Phase;
use App\Importer\AbstractProcessor;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @TODO 'entrants' and 'entrants_players' tables are not automatically cleared when no longer necessary.
 */
class EntrantProcessor extends AbstractProcessor
{
    /**
     * @var Entrant[]
     */
    protected $entrants = [];

    /**
     * @param int $entrantId
     *
     * @return bool
     */
    public function hasEntrant($entrantId)
    {
        return array_key_exists($entrantId, $this->entrants);
    }

    /**
     * @param int $entrantId
     *
     * @return Entrant
     */
    public function findEntrant($entrantId)
    {
        if ($this->hasEntrant($entrantId)) {
            return $this->entrants[$entrantId];
        }

        return null;
    }

    /**
     * @param array           $entrantData
     * @param PlayerProcessor $playerProcessor
     * @param Phase           $phase
     *
     * @TODO Also remove players that are no longer part of the entrant.
     */
    public function processNew(array $entrantData, PlayerProcessor $playerProcessor, Phase $phase = null)
    {
        $entrantId = $entrantData['id'];

        if ($this->hasEntrant($entrantId)) {
            return;
        }

        $entrant = $this->entityManager->getRepository('App:Entrant')->findOneBy([
            'externalId' => $entrantId,
        ]);

        if (!$entrant instanceof Entrant) {
            $entrant = new Entrant();
            $entrant->setExternalId(strval($entrantId));
            $entrant->setIsNew(false);

            $this->entityManager->persist($entrant);
        }

        $entrant->setName($entrantData['name']);
        $entrant->setOriginPhase($phase);

        foreach ($entrantData['playerIds'] as $playerId) {
            $player = $playerProcessor->findPlayer($playerId);

            if (!$entrant->hasPlayer($player)) {
                $entrant->addPlayer($player);
            }
        }

        $this->entrants[$entrantId] = $entrant;
    }
}
