<?php

declare(strict_types=1);

namespace CoreBundle\DataTransferObject;

use CoreBundle\Entity\Phase;
use JMS\Serializer\Annotation as Serializer;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PhaseDTO
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
     * @var EventDTO
     */
    public $event;

    /**
     * @param Phase $phase
     */
    public function __construct(Phase $phase)
    {
        $this->id = $phase->getId();
        $this->name = $phase->getName();
        $this->event = new EventDTO($phase->getEvent());
    }
}
