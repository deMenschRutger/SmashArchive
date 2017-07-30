<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Smashgg;

use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Importer
{
    /**
     * @param string       $smashggId
     * @param array        $eventIds
     * @param SymfonyStyle $io
     */
    public function import($smashggId, $eventIds, SymfonyStyle $io)
    {
        var_dump($smashggId);
        var_dump($eventIds);
    }
}
