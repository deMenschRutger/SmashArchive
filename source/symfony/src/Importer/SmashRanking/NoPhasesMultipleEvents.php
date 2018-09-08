<?php

declare(strict_types = 1);

namespace App\Importer\SmashRanking;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class NoPhasesMultipleEvents extends PhasesMultipleEvents
{
    /**
     * @return void
     */
    public function importWithConfiguration()
    {
        $this->import(false, true, true);
    }
}
