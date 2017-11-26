<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Challonge\Processor;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\Phase;
use CoreBundle\Importer\AbstractProcessor;
use Reflex\Challonge\Models\Participant;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
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
     * @param Participant $entrantData
     * @param Phase       $phase
     */
    public function processNew(Participant $entrantData, Phase $phase)
    {
        $entrantId = $entrantData->{'id'};

        if ($this->hasEntrant($entrantId)) {
            return;
        }

        $entrant = $this->entityManager->getRepository('CoreBundle:Entrant')->findOneBy([
            'externalId' => $entrantId,
        ]);

        if (!$entrant instanceof Entrant) {
            $entrant = new Entrant();
            $entrant->setExternalId($entrantId);

            $this->entityManager->persist($entrant);
        }

        $entrant->setName($entrantData->{'name'});
        $entrant->setOriginPhase($phase);

        $this->entrants[$entrantId] = $entrant;
    }
}
