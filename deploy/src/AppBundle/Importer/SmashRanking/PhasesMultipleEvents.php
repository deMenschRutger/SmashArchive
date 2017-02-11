<?php

declare(strict_types=1);

namespace AppBundle\Importer\SmashRanking;

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
     * @param bool $hasPhases
     * @param bool $hasMultipleEvents
     * @param bool $isBracket
     * @return array
     */
    /*protected function getEvents(bool $hasPhases, bool $hasMultipleEvents, bool $isBracket)
    {
        $events = parent::getEvents($hasPhases, $hasMultipleEvents, $isBracket);

        foreach ($events as $tournamentName => $tournament) {
            $types = [];

            foreach ($tournament['events'] as $event) {
                if (!in_array($event['type'], $types)) {
                    $types[] = $event['type'];
                }
            }




            var_dump($types); die;



        }
    }*/
}