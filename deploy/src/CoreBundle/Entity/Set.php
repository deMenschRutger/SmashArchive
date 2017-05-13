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
     * @TODO This needs to become a 'status' field that can have values like 'forfeit' and 'DQ'.
     *
     * @ORM\Column(name="is_forfeit", type="boolean")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $isForfeit = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_ranked", type="boolean")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $isRanked = true;

    /**
     * @var PhaseGroup
     *
     * @ORM\ManyToOne(targetEntity="PhaseGroup", inversedBy="sets")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $phaseGroup;

    /**
     * @var Entrant
     *
     * @ORM\ManyToOne(targetEntity="Entrant", inversedBy="entrantOneSets")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $entrantOne;

    /**
     * @var Entrant
     *
     * @ORM\ManyToOne(targetEntity="Entrant", inversedBy="entrantTwoSets")
     *
     * @Serializer\Groups({"players_sets"})
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
     * @var string
     *
     * @ORM\Column(name="round_name", type="string", length=255, nullable=true)
     */
    private $roundName;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_grand_finals", type="boolean")
     */
    private $isGrandFinals = false;

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
    public function getIsGrandFinals(): bool
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
}
