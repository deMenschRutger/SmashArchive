<?php

declare(strict_types=1);

namespace AppBundle\Importer\SmashRanking;

use CoreBundle\Entity\Event;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Tournament;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PhasesMultipleEvents extends AbstractScenario
{
    /**
     * @return void
     */
    public function importWithConfiguration()
    {
        $this->import(true, true, true);
    }

    /**
     * @param array $events
     * @param array $tournaments
     * @return array
     */
    protected function processEvents(array $events, array $tournaments)
    {
        $phaseGroups = [];

        foreach ($events as $tournamentId => $tournament) {
            /** @var Tournament $tournamentEntity */
            $tournamentEntity = $tournaments[$tournamentId];
            $tournamentEntity->setIsComplete(false);
            $tournamentEntity->setIsActive(false);

            $entity = new Event();
            $entity->setName('Unidentified event');
            $entity->setGame($this->melee);
            $entity->setTournament($tournamentEntity);

            $this->entityManager->persist($entity);

            $phase = new Phase();
            $phase->setName('Unidentified phase');
            $phase->setEvent($entity);
            $phase->setPhaseOrder(0);

            $this->entityManager->persist($phase);

            foreach ($tournament['events'] as $eventId => $event) {
                $name = 'Unidentified phase group';

                if ($event['name_bracket']) {
                    $name = $event['name_bracket'];
                } elseif ($event['pool']) {
                    $name = $event['pool'];
                }

                $resultsUrl = $event['result_page'] ? $event['result_page'] : null;
                $smashRankingInfo = \GuzzleHttp\json_encode($event, JSON_PRETTY_PRINT);

                $phaseGroup = new PhaseGroup();
                $phaseGroup->setName($name);
                $phaseGroup->setType(0);
                $phaseGroup->setPhase($phase);
                $phaseGroup->setResultsUrl($resultsUrl);
                $phaseGroup->setSmashRankingInfo($smashRankingInfo);

                $this->entityManager->persist($phaseGroup);

                $phaseGroups[$eventId] = $phaseGroup;
            }
        }

        return $phaseGroups;
    }
}