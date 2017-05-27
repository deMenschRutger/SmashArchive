<?php

declare(strict_types = 1);

namespace Domain\Handler\Tournament\Import;

use Domain\Command\Tournament\Import\SmashggCommand;
use Domain\Handler\AbstractHandler;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SmashggHandler extends AbstractHandler
{
    /**
     * @param SmashggCommand $command
     */
    public function handle(SmashggCommand $command)
    {
        var_dump($command);
    }
}
