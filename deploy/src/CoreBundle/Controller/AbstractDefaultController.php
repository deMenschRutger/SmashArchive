<?php

declare(strict_types=1);

namespace CoreBundle\Controller;

use League\Tactician\CommandBus;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractDefaultController extends Controller
{
    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @return CommandBus
     */
    public function getCommandBus()
    {
        return $this->commandBus;
    }

    /**
     * @param CommandBus $commandBus
     */
    public function setCommandBus($commandBus)
    {
        $this->commandBus = $commandBus;
    }
}
