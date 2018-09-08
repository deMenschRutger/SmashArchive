<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Smashgg\Processor;

use CoreBundle\Entity\Entrant;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Set;
use CoreBundle\Entity\Tournament;
use CoreBundle\Importer\AbstractProcessor;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SetProcessor extends AbstractProcessor
{
    /**
     * @var Set[]
     */
    protected $sets = [];

    /**
     * @param int $setId
     * @return bool
     */
    public function hasSet($setId)
    {
        return array_key_exists($setId, $this->sets);
    }

    /**
     * @param int $setId
     * @return Set
     */
    public function findSet($setId)
    {
        if ($this->hasSet($setId)) {
            return $this->sets[$setId];
        }

        return null;
    }

    /**
     * @param array            $setData
     * @param EntrantProcessor $entrantProcessor
     * @param PhaseGroup       $phaseGroup
     */
    public function processNew(array $setData, EntrantProcessor $entrantProcessor, PhaseGroup $phaseGroup = null)
    {
        $setId = $setData['id'];

        if ($this->hasSet($setId)) {
            return;
        }

        $set = $this->entityManager->getRepository('CoreBundle:Set')->findOneBy([
            'externalId' => $setId,
        ]);

        if (!$set instanceof Set) {
            $set = new Set();
            $set->setExternalId($setId);

            $this->entityManager->persist($set);
        }

        $set->setRound($setData['originalRound']);
        $set->setPhaseGroup($phaseGroup);

        $entrantOne = $entrantProcessor->findEntrant($setData['entrant1Id']);
        $entrantTwo = $entrantProcessor->findEntrant($setData['entrant2Id']);

        if ($entrantOne) {
            $set->setEntrantOne($entrantOne);
        }

        if ($entrantTwo) {
            $set->setEntrantTwo($entrantTwo);
        }

        if ($setData['winnerId'] && $setData['winnerId'] == $setData['entrant1Id']) {
            $set->setWinner($entrantOne);
            $set->setWinnerScore($setData['entrant1Score']);
            $set->setLoser($entrantTwo);
            $set->setLoserScore($setData['entrant2Score']);
        } elseif ($setData['winnerId'] && $setData['winnerId'] == $setData['entrant2Id']) {
            $set->setWinner($entrantTwo);
            $set->setWinnerScore($setData['entrant2Score']);
            $set->setLoser($entrantOne);
            $set->setLoserScore($setData['entrant1Score']);
        }

        if ($set->getEntrantOne() instanceof Entrant && $set->getEntrantTwo() instanceof Entrant && $set->getLoserScore() === -1) {
            $set->setStatus(Set::STATUS_DQED);
            $set->setIsRanked(false);
        } elseif ($set->getWinner() === null && $set->getLoser() === null) {
            $set->setStatus(Set::STATUS_NOT_PLAYED);
            $set->setIsRanked(false);
        } elseif ($set->getWinner() instanceof Entrant && $set->getLoser() === null) {
            $set->setStatus(Set::STATUS_NOT_PLAYED);
            $set->setIsRanked(false);
        }

        $this->sets[$setId] = $set;
    }

    /**
     * @param Tournament $tournament
     */
    public function cleanUp(Tournament $tournament)
    {
        $sets = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('s')
            ->from('CoreBundle:Set', 's')
            ->join('s.phaseGroup', 'pg')
            ->join('pg.phase', 'p')
            ->join('p.event', 'e')
            ->join('e.tournament', 't')
            ->where('t.id = :tournamentId')
            ->setParameter('tournamentId', $tournament->getId())
            ->getQuery()
            ->getResult()
        ;

        /** @var Set[] $sets */
        foreach ($sets as $set) {
            $setId = $set->getExternalId();

            if ($this->hasSet($setId)) {
                continue;
            }

            $this->entityManager->remove($set);
        }
    }
}
