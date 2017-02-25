<?php

declare(strict_types=1);

namespace CoreBundle\DataTransferObject;

use CoreBundle\Entity\PhaseGroup;
use JMS\Serializer\Annotation as Serializer;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PhaseGroupDTO
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $name;

    /**
     * @var PhaseDTO
     */
    public $phase;

    /**
     * @param PhaseGroup $phaseGroup
     */
    public function __construct(PhaseGroup $phaseGroup)
    {
        $this->id = $phaseGroup->getId();
        $this->name = $phaseGroup->getName();
        $this->phase = new PhaseDTO($phaseGroup->getPhase());
    }
}
