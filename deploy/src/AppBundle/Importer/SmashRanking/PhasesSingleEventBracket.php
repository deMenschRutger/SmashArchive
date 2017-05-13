<?php

declare(strict_types = 1);

namespace AppBundle\Importer\SmashRanking;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PhasesSingleEventBracket extends AbstractScenario
{
    /**
     * @return void
     */
    public function importWithConfiguration()
    {
        $this->import(true, false, true);
    }
}