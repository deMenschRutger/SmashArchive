<?php

declare(strict_types = 1);

namespace AppBundle\Importer\SmashRanking;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PhasesSingleEventNoBracket extends AbstractScenario
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
        $this->import(true, false, false);
    }
}