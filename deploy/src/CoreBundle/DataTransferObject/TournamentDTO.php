<?php

declare(strict_types=1);

namespace CoreBundle\DataTransferObject;

use CoreBundle\Entity\Tournament;
use JMS\Serializer\Annotation as Serializer;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class TournamentDTO
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
    public $name;

    /**
     * @param Tournament $tournament
     */
    public function __construct(Tournament $tournament)
    {
        $this->id = $tournament->getId();
        $this->slug = $tournament->getSlug();
        $this->name = $tournament->getName();
    }
}
