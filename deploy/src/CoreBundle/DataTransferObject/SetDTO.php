<?php

declare(strict_types=1);

namespace CoreBundle\DataTransferObject;

use CoreBundle\Entity\Set;
use JMS\Serializer\Annotation as Serializer;

/**
 * @author Rutger Mensch <rutger@mediamonks.com>
 */
class SetDTO
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
     * @Serializer\Type("integer")
     */
    public $round;

    /**
     * @param Set $set
     */
    public function __construct(Set $set)
    {
        $this->id = $set->getId();
        $this->round = $set->getRound();
    }
}
