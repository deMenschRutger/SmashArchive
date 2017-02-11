<?php

declare(strict_types=1);

namespace AppBundle\Importer\SmashRanking;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class NoPhasesSingleEventNoBracket extends AbstractScenario
{
    /**
     * @var string
     */
    protected $defaultPhaseGroupsName = 'Round Robin Pool';

    /**
     * @return void
     */
    public function importWithConfiguration()
    {
        $this->import(false, false, false);
    }
}