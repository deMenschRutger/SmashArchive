<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Smashgg\Processor;

use CoreBundle\Entity\Entrant;

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
     * @return bool
     */
    public function hasEntrant($entrantId)
    {
        return array_key_exists($entrantId, $this->entrants);
    }

    /**
     * @param int $entrantId
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
     *
     * @TODO Also remove players that are no longer part of the entrant.
     */
    public function processNew(array $entrantData, PlayerProcessor $playerProcessor)
    {
        $entrantId = $entrantData['id'];

        if ($this->hasEntrant($entrantId)) {
            return;
        }

        $entrant = $this->entityManager->getRepository('CoreBundle:Entrant')->findOneBy([
            'smashggId' => $entrantId,
        ]);

        if (!$entrant instanceof Entrant) {
            $entrant = new Entrant();
            $entrant->setSmashggId($entrantId);

            $this->entityManager->persist($entrant);
        }

        $entrant->setName($entrantData['name']);

        foreach ($entrantData['playerIds'] as $playerId) {
            $player = $playerProcessor->findPlayer($playerId);

            if (!$entrant->hasPlayer($player)) {
                $entrant->addPlayer($player);
            }
        }
        $this->entrants[$entrantId] = $entrant;
    }
}
