<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Smashgg\Processor;

use CoreBundle\Entity\Event;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\Tournament;
use CoreBundle\Importer\AbstractProcessor;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PhaseProcessor extends AbstractProcessor
{
    /**
     * @var Phase[]
     */
    protected $phases = [];

    /**
     * @param int $phaseId
     * @return bool
     */
    public function hasPhase($phaseId)
    {
        return array_key_exists($phaseId, $this->phases);
    }

    /**
     * @param int $phaseId
     * @return Phase
     */
    public function findPhase($phaseId)
    {
        if ($this->hasPhase($phaseId)) {
            return $this->phases[$phaseId];
        }

        return null;
    }

    /**
     * @return Phase[]
     */
    public function getAllPhases()
    {
        return $this->phases;
    }

    /**
     * @param array $phaseData
     * @param Event $event
     */
    public function processNew(array $phaseData, Event $event)
    {
        $phaseId = $phaseData['id'];

        if ($this->hasPhase($phaseId)) {
            return;
        }

        $phase = $this->entityManager->getRepository('CoreBundle:Phase')->findOneBy([
            'smashggId' => $phaseId,
        ]);

        if (!$phase instanceof Phase) {
            $phase = new Phase();
            $phase->setExternalId($phaseId);

            $this->entityManager->persist($phase);
        }

        $phase->setEvent($event);
        $phase->setName($phaseData['name']);
        $phase->setPhaseOrder($phaseData['phaseOrder']);

        $this->phases[$phaseId] = $phase;
    }

    /**
     * @param Tournament $tournament
     */
    public function cleanUp(Tournament $tournament)
    {
        $phases = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('p')
            ->from('CoreBundle:Phase', 'p')
            ->join('p.event', 'e')
            ->join('e.tournament', 't')
            ->where('t.id = :tournamentId')
            ->setParameter('tournamentId', $tournament->getId())
            ->getQuery()
            ->getResult()
        ;

        /** @var Phase[] $phases */
        foreach ($phases as $phase) {
            $phaseId = $phase->getExternalId();

            if ($this->hasPhase($phaseId)) {
                continue;
            }

            $this->entityManager->remove($phase);
        }
    }
}
