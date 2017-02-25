<?php

declare(strict_types=1);

namespace CoreBundle\DataTransferObject;

use CoreBundle\Entity\Player;
use JMS\Serializer\Annotation as Serializer;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PlayerDTO
{
    /**
     * @var integer
     */
    public $id;

    /**
     * @var string
     */
    public $slug;

    /**
     * @var string
     */
    public $gamerTag;

    /**
     * @var string
     */
    public $name;

    /**
     * @param Player $player
     */
    public function __construct(Player $player)
    {
        $this->id = $player->getId();
        $this->slug = $player->getSlug();
        $this->gamerTag = $player->getGamerTag();
        $this->name = $player->getName();
    }
}
