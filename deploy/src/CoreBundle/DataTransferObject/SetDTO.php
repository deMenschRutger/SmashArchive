<?php

declare(strict_types=1);

namespace CoreBundle\DataTransferObject;

use CoreBundle\Entity\Set;
use JMS\Serializer\Annotation as Serializer;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
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
     * @var int
     *
     * @Serializer\Type("integer")
     */
    public $round;

    /**
     * @var PlayerDTO
     */
    public $entrantOne;

    /**
     * @var PlayerDTO
     */
    public $entrantTwo;

    /**
     * @var PlayerDTO
     */
    public $winner;

    /**
     * @var PlayerDTO
     */
    public $loser;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     */
    public $winnerScore;

    /**
     * @var int
     *
     * @Serializer\Type("integer")
     */
    public $loserScore;

    /**
     * @var bool
     */
    public $isForfeit;

    /**
     * @var bool
     */
    public $isRanked;

    /**
     * @var PhaseGroupDTO
     */
    public $phaseGroup;

    /**
     * @param Set $set
     */
    public function __construct(Set $set)
    {
        $this->id = $set->getId();
        $this->round = $set->getRound();
        $this->entrantOne = new EntrantDTO($set->getEntrantOne());
        $this->entrantTwo = new EntrantDTO($set->getEntrantTwo());
        $this->winner = new EntrantDTO($set->getWinner());
        $this->loser = new EntrantDTO($set->getLoser());
        $this->winnerScore = $set->getWinnerScore();
        $this->loserScore = $set->getLoserScore();
        $this->isForfeit = $set->getIsForfeit();
        $this->isRanked = $set->getIsRanked();
        $this->phaseGroup = new PhaseGroupDTO($set->getPhaseGroup());
    }
}
