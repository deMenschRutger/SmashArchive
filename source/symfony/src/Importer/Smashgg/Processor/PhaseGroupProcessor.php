<?php

declare(strict_types = 1);

namespace App\Importer\Smashgg\Processor;

use App\Entity\Phase;
use App\Entity\PhaseGroup;
use App\Entity\Tournament;
use App\Importer\AbstractProcessor;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PhaseGroupProcessor extends AbstractProcessor
{
    /**
     * @var PhaseGroup[]
     */
    protected $phaseGroups = [];

    /**
     * @param int $phaseGroupId
     *
     * @return bool
     */
    public function hasPhaseGroup($phaseGroupId)
    {
        return array_key_exists($phaseGroupId, $this->phaseGroups);
    }

    /**
     * @param int $phaseGroupId
     *
     * @return PhaseGroup
     */
    public function findPhaseGroup($phaseGroupId)
    {
        if ($this->hasPhaseGroup($phaseGroupId)) {
            return $this->phaseGroups[$phaseGroupId];
        }

        return null;
    }

    /**
     * @param array $phaseGroupData
     * @param Phase $phase
     */
    public function processNew(array $phaseGroupData, Phase $phase)
    {
        $phaseGroupId = $phaseGroupData['id'];

        if ($this->hasPhaseGroup($phaseGroupId)) {
            return;
        }

        $phaseGroup = $this->entityManager->getRepository('App:PhaseGroup')->findOneBy([
            'externalId' => $phaseGroupId,
        ]);

        if (!$phaseGroup instanceof PhaseGroup) {
            $phaseGroup = new PhaseGroup();
            $phaseGroup->setExternalId(strval($phaseGroupId));

            $this->entityManager->persist($phaseGroup);
        }

        $phaseGroup->setPhase($phase);
        $phaseGroup->setName($phaseGroupData['displayIdentifier']);
        $phaseGroup->setType($phaseGroupData['groupTypeId']);

        $this->phaseGroups[$phaseGroupId] = $phaseGroup;
    }

    /**
     * @param Tournament $tournament
     */
    public function cleanUp(Tournament $tournament)
    {
        $phaseGroups = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('pg')
            ->from('App:PhaseGroup', 'pg')
            ->join('pg.phase', 'p')
            ->join('p.event', 'e')
            ->join('e.tournament', 't')
            ->where('t.id = :tournamentId')
            ->setParameter('tournamentId', $tournament->getId())
            ->getQuery()
            ->getResult()
        ;

        /** @var PhaseGroup[] $phaseGroups */
        foreach ($phaseGroups as $phaseGroup) {
            $phaseGroupId = $phaseGroup->getExternalId();

            if ($this->hasPhaseGroup($phaseGroupId)) {
                continue;
            }

            $this->entityManager->remove($phaseGroup);
        }
    }
}
