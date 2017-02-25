<?php

declare(strict_types=1);

namespace CoreBundle\DataTransferObject;

use CoreBundle\Entity\Entrant;
use JMS\Serializer\Annotation as Serializer;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class EntrantDTO
{
    /**
     * @var integer
     *
     * @Serializer\Type("integer")
     */
    public $id;

    /**
     * @var string
     *
     * @Serializer\Type("string")
     */
    public $name;

    /**
     * @param Entrant $entrant
     */
    public function __construct(Entrant $entrant)
    {
        $this->id = $entrant->getId();
        $this->name = $entrant->getName();
    }
}
