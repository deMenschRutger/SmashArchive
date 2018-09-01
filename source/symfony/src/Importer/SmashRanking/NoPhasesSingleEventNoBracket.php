<?php

declare(strict_types = 1);

namespace App\Importer\SmashRanking;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class NoPhasesSingleEventNoBracket extends AbstractScenario
{
    /**
     * @var string
     */
    protected $defaultPhaseName = 'Round Robin Pools';

    /**
     * @return void
     */
    public function importWithConfiguration()
    {
        $this->import(false, false, false);
    }
}
