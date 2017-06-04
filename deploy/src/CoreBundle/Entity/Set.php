<?php

declare(strict_types = 1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="phase_group_set", indexes={
 *     @ORM\Index(name="smashgg_index", columns={"smashgg_id"}),
 *     @ORM\Index(name="round_index", columns={"round"}),
 *     @ORM\Index(name="is_ranked_index", columns={"is_ranked"}),
 *     @ORM\Index(name="created_at_index", columns={"created_at"}),
 *     @ORM\Index(name="updated_at_index", columns={"updated_at"}),
 * })
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\SetRepository")
 */
class Set
{
    const STATUS_PLAYED = 'played';
    const STATUS_NOT_PLAYED = 'not_played';
    const STATUS_FORFEITED = 'forfeit';
    const STATUS_DQED = 'dqed';

    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"players_sets"})
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
     * @ORM\Column(name="smashgg_id", type="string", length=255, nullable=true)
     */
    private $smashggId;

    /**
     * @var int
     *
     * @ORM\Column(name="round", type="integer")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $round;

    /**
     * @var string
     *
     * @ORM\Column(name="round_name", type="string", length=255, nullable=true)
     */
    private $roundName;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_finals", type="boolean")
     */
    private $isFinals = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_grand_finals", type="boolean")
     */
    private $isGrandFinals = false;

    /**
     * @var int
     *
     * @ORM\Column(name="winner_score", type="integer", nullable=true)
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $winnerScore;

    /**
     * @var int
     *
     * @ORM\Column(name="loser_score", type="integer", nullable=true)
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $loserScore;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_ranked", type="boolean")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $isRanked = true;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $status = self::STATUS_PLAYED;

    /**
     * @var PhaseGroup
     *
     * @ORM\ManyToOne(targetEntity="PhaseGroup", inversedBy="sets")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $phaseGroup;

    /**
     * @var Entrant
     *
     * @ORM\ManyToOne(targetEntity="Entrant", inversedBy="entrantOneSets")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $entrantOne;

    /**
     * @var Entrant
     *
     * @ORM\ManyToOne(targetEntity="Entrant", inversedBy="entrantTwoSets")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $entrantTwo;

    /**
     * @var Entrant
     *
     * @ORM\ManyToOne(targetEntity="Entrant")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $winner;

    /**
     * @var Entrant
     *
     * @ORM\ManyToOne(targetEntity="Entrant")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $loser;

    /**
     * @var int
     */
    private $loserRank;

    /**
     * @return string
     */
    public function __toString()
    {
        return sprintf('%s vs %s (%s)', $this->getEntrantOneName(), $this->getEntrantTwoName(), $this->getRoundName());
    }

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
     * @param string $smashggId
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
     * @return string
     */
    public function getRoundName()
    {
        return $this->roundName;
    }

    /**
     * @param string $roundName
     */
    public function setRoundName($roundName)
    {
        $this->roundName = $roundName;
    }

    /**
     * @return bool
     */
    public function isFinals(): bool
    {
        return $this->isFinals;
    }

    /**
     * @param bool $isFinals
     */
    public function setIsFinals(bool $isFinals)
    {
        $this->isFinals = $isFinals;
    }

    /**
     * @return bool
     */
    public function isGrandFinals(): bool
    {
        return $this->isGrandFinals;
    }

    /**
     * @param bool $isGrandFinals
     */
    public function setIsGrandFinals(bool $isGrandFinals)
    {
        $this->isGrandFinals = $isGrandFinals;
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
     * @return string
     */
    public function getStatus(): string
    {
        return $this->status;
    }

    /**
     * @return bool
     */
    public function wasPlayed(): bool
    {
        return $this->status === self::STATUS_PLAYED;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
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
        $phaseGroup->addSet($this);
    }

    /**
     * @return Entrant
     */
    public function getEntrantOne()
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
     * @return string
     */
    public function getEntrantOneName()
    {
        if ($this->entrantOne instanceof Entrant) {
            return $this->entrantOne->getName();
        }

        return 'bye';
    }

    /**
     * @return string
     */
    public function getEntrantOneScore()
    {
        if ($this->entrantOne instanceof Entrant && $this->entrantOne === $this->winner) {
            return $this->getWinnerScore();
        }

        return $this->getLoserScore();
    }

    /**
     * @return Entrant
     */
    public function getEntrantTwo()
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
     * @return string
     */
    public function getEntrantTwoName()
    {
        if ($this->entrantTwo instanceof Entrant) {
            return $this->entrantTwo->getName();
        }

        return 'bye';
    }

    /**
     * @return string
     */
    public function getEntrantTwoScore()
    {
        if ($this->entrantTwo instanceof Entrant && $this->entrantTwo === $this->winner) {
            return $this->getWinnerScore();
        }

        return $this->getLoserScore();
    }

    /**
     * @param Entrant $entrant
     * @return bool
     */
    public function hasEntrant(Entrant $entrant)
    {
        return $entrant === $this->entrantOne || $entrant === $this->entrantTwo;
    }

    /**
     * @return Entrant
     */
    public function getWinner()
    {
        return $this->winner;
    }

    /**
     * @return string
     *
     * @Serializer\Groups({"players_sets"})
     * @Serializer\SerializedName("winner")
     * @Serializer\VirtualProperty()
     */
    public function getWinnerId()
    {
        return $this->winner->getId();
    }

    /**
     * @param Entrant $winner
     */
    public function setWinner(Entrant $winner = null)
    {
        $this->winner = $winner;
    }

    /**
     * @param Entrant $entrant
     * @return bool
     */
    public function isWinner(Entrant $entrant)
    {
        return $entrant === $this->winner;
    }

    /**
     * @return Entrant
     */
    public function getLoser()
    {
        return $this->loser;
    }

    /**
     * @return string
     *
     * @Serializer\Groups({"players_sets"})
     * @Serializer\SerializedName("loser")
     * @Serializer\VirtualProperty()
     */
    public function getLoserId()
    {
        return $this->loser->getId();
    }

    /**
     * @param Entrant $loser
     */
    public function setLoser(Entrant $loser = null)
    {
        $this->loser = $loser;
    }

    /**
     * @param Entrant $entrant
     * @return bool
     */
    public function isLoser(Entrant $entrant)
    {
        return $entrant === $this->loser;
    }

    /**
     * @return int
     */
    public function getLoserRank()
    {
        return $this->loserRank;
    }

    /**
     * @param int $loserRank
     */
    public function setLoserRank($loserRank)
    {
        $this->loserRank = intval($loserRank);
    }
}
