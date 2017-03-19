<?php

declare(strict_types=1);

namespace CoreBundle\Bracket;

use CoreBundle\Entity\PhaseGroup;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractBracket
{
    /**
     * @var PhaseGroup
     */
    protected $phaseGroup;

    /**
     * @param PhaseGroup $phaseGroup
     */
    public function __construct(PhaseGroup $phaseGroup)
    {
        $this->phaseGroup = $phaseGroup;

        $this->init();
    }

    /**
     * @return void
     */
    abstract protected function init();
}