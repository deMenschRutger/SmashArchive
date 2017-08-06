<?php

declare(strict_types = 1);

namespace AdminBundle\Utility;

use CoreBundle\Entity\Player;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PlayerMerger
{
    /**
     * @var Player
     */
    protected $sourcePlayer;

    /**
     * @var Player
     */
    protected $targetPlayer;

    /**
     * @var array
     */
    protected $properties = [
        'id',
        'slug',
        'smashggId',
        'gamerTag',
        'name',
        'region',
        'city',
    ];

    /**
     * @param Player $sourcePlayer
     * @param Player $targetPlayer
     */
    public function __construct(Player $sourcePlayer, Player $targetPlayer)
    {
        $this->sourcePlayer = $sourcePlayer;
        $this->targetPlayer = $targetPlayer;
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

    /**
     * @param string $property
     * @return string
     */
    public function getSourceProperty(string $property)
    {
        $getter = 'get'.ucfirst($property);

        return $this->sourcePlayer->$getter();
    }

    /**
     * @param string $property
     * @return string
     */
    public function getTargetProperty(string $property)
    {
        $getter = 'get'.ucfirst($property);

        return $this->targetPlayer->$getter();
    }

    /**
     * @param string $property
     * @return string
     */
    public function getResultProperty(string $property)
    {
        $targetProperty = $this->getTargetProperty($property);

        if ($targetProperty && $targetProperty !== 0) {
            return $targetProperty;
        }

        return $this->getSourceProperty($property);
    }
}
