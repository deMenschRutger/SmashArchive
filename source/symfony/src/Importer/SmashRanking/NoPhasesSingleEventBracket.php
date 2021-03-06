<?php

declare(strict_types = 1);

namespace App\Importer\SmashRanking;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class NoPhasesSingleEventBracket extends AbstractScenario
{
    /**
     * @return void
     */
    public function importWithConfiguration()
    {
        $this->import(false, false, true);
    }
}
