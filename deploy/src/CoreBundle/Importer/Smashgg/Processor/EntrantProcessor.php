<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Smashgg\Processor;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Event;
use CoreBundle\Importer\AbstractProcessor;

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
     * @param Event           $event
     *
     * @TODO Also remove players that are no longer part of the entrant.
     */
    public function processNew(array $entrantData, PlayerProcessor $playerProcessor, Event $event = null)
    {
        $entrantId = $entrantData['id'];

        if ($this->hasEntrant($entrantId)) {
            return;
        }

        $entrant = $this->entityManager->getRepository('CoreBundle:Entrant')->findOneBy([
            'externalId' => $entrantId,
        ]);

        if (!$entrant instanceof Entrant) {
            $entrant = new Entrant();
            $entrant->setExternalId($entrantId);
            $entrant->setIsNew(false);

            $this->entityManager->persist($entrant);
        }

        $entrant->setName($entrantData['name']);
        $entrant->setOriginEvent($event);

        foreach ($entrantData['playerIds'] as $playerId) {
            $player = $playerProcessor->findPlayer($playerId);

            if (!$entrant->hasPlayer($player)) {
                $entrant->addPlayer($player);
            }
        }

        $this->entrants[$entrantId] = $entrant;
    }
}
