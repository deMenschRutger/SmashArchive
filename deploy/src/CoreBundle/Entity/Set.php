<?php

declare(strict_types=1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="phase_group_set")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\SetRepository")
 */
class Set
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * The original ID from the SmashRanking database.
     *
     * @var int
     *
     * @ORM\Column(name="original_id", type="integer", nullable=true)
     */
    private $originalId;

    /**
     * @var string
     *
     * @ORM\Column(name="smashgg_id", type="integer", nullable=true)
     */
    private $smashggId;

    /**
     * @var int
     *
     * @ORM\Column(name="round", type="integer")
     */
    private $round;

    /**
     * @var int
     *
     * @ORM\Column(name="winner_score", type="integer", nullable=true)
     */
    private $winnerScore;

    /**
     * @var int
     *
     * @ORM\Column(name="loser_score", type="integer", nullable=true)
     */
    private $loserScore;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_forfeit", type="boolean")
     */
    private $isForfeit = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_ranked", type="boolean")
     */
    private $isRanked = true;

    /**
     * @var PhaseGroup
     *
     * @ORM\ManyToOne(targetEntity="PhaseGroup", inversedBy="sets")
     */
    private $phaseGroup;

    /**
     * @var Entrant
     *
     * @ORM\ManyToOne(targetEntity="Entrant", inversedBy="entrantOneSets")
     */
    private $entrantOne;

    /**
     * @var Entrant
     *
     * @ORM\ManyToOne(targetEntity="Entrant", inversedBy="entrantTwoSets")
     */
    private $entrantTwo;

    /**
     * @var Entrant
     *
     * @ORM\ManyToOne(targetEntity="Entrant")
     */
    private $winner;

    /**
     * @var Entrant
     *
     * @ORM\ManyToOne(targetEntity="Entrant")
     */
    private $loser;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getOriginalId()
    {
        return $this->originalId;
    }

    /**
     * @param int $originalId
     */
    public function setOriginalId($originalId)
    {
        $this->originalId = $originalId;
    }

    /**
     * @return string
     */
    public function getSmashggId()
    {
        return $this->smashggId;
    }

    /**
     * @param int $smashggId
     */
    public function setSmashggId($smashggId)
    {
        $this->smashggId = $smashggId;
    }

    /**
     * @return int
     */
    public function getRound(): int
    {
        return $this->round;
    }

    /**
     * @param int $round
     */
    public function setRound(int $round)
    {
        $this->round = $round;
    }

    /**
     * @return int
     */
    public function getWinnerScore()
    {
        return $this->winnerScore;
    }

    /**
     * @param int $winnerScore
     */
    public function setWinnerScore($winnerScore)
    {
        $this->winnerScore = $winnerScore;
    }

    /**
     * @return int
     */
    public function getLoserScore()
    {
        return $this->loserScore;
    }

    /**
     * @param int $loserScore
     */
    public function setLoserScore($loserScore)
    {
        $this->loserScore = $loserScore;
    }

    /**
     * @return bool
     */
    public function getIsForfeit(): bool
    {
        return $this->isForfeit;
    }

    /**
     * @param bool $isForfeit
     */
    public function setIsForfeit(bool $isForfeit)
    {
        $this->isForfeit = $isForfeit;
    }

    /**
     * @return bool
     */
    public function getIsRanked(): bool
    {
        return $this->isRanked;
    }

    /**
     * @param bool $isRanked
     */
    public function setIsRanked(bool $isRanked)
    {
        $this->isRanked = $isRanked;
    }

    /**
     * @return PhaseGroup
     */
    public function getPhaseGroup(): PhaseGroup
    {
        return $this->phaseGroup;
    }

    /**
     * @param PhaseGroup $phaseGroup
     */
    public function setPhaseGroup(PhaseGroup $phaseGroup)
    {
        $this->phaseGroup = $phaseGroup;
    }

    /**
     * @return Entrant
     */
    public function getEntrantOne(): Entrant
    {
        return $this->entrantOne;
    }

    /**
     * @param Entrant $entrantOne
     */
    public function setEntrantOne(Entrant $entrantOne)
    {
        $this->entrantOne = $entrantOne;
    }

    /**
     * @return Entrant
     */
    public function getEntrantTwo(): Entrant
    {
        return $this->entrantTwo;
    }

    /**
     * @param Entrant $entrantTwo
     */
    public function setEntrantTwo(Entrant $entrantTwo)
    {
        $this->entrantTwo = $entrantTwo;
    }

    /**
     * @return Entrant
     */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * @param Entrant $winner
     */
    public function setWinner(Entrant $winner = null)
    {
        $this->winner = $winner;
    }

    /**
     * @return Entrant
     */
    public function getLoser()
    {
        return $this->loser;
    }

    /**
     * @param Entrant $loser
     */
    public function setLoser(Entrant $loser = null)
    {
        $this->loser = $loser;
    }
}

