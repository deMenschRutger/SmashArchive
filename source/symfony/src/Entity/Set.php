<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="phase_group_set", indexes={
 *     @ORM\Index(name="external_id_index", columns={"external_id"}),
 *     @ORM\Index(name="round_index", columns={"round"}),
 *     @ORM\Index(name="is_ranked_index", columns={"is_ranked"}),
 *     @ORM\Index(name="created_at_index", columns={"created_at"}),
 *     @ORM\Index(name="updated_at_index", columns={"updated_at"}),
 * })
 * @ORM\Entity(repositoryClass="App\Repository\SetRepository")
 *
 * @Serializer\ExclusionPolicy("all")
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
     * @Serializer\Expose()
     * @Serializer\Groups({"profiles_sets"})
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="external_id", type="string", length=255, nullable=true)
     */
    private $externalId;

    /**
     * @var int
     *
     * @ORM\Column(name="round", type="integer")
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"profiles_sets"})
     */
    private $round;

    /**
     * @var string|null
     *
     * @ORM\Column(name="round_name", type="string", length=255, nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"profiles_sets"})
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
     * @var int|null
     *
     * @ORM\Column(name="winner_score", type="integer", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"profiles_sets"})
     */
    private $winnerScore;

    /**
     * @var int|null
     *
     * @ORM\Column(name="loser_score", type="integer", nullable=true)
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"profiles_sets"})
     */
    private $loserScore;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_ranked", type="boolean")
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"profiles_sets"})
     */
    private $isRanked = true;

    /**
     * @var string
     *
     * @ORM\Column(name="status", type="string")
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"profiles_sets"})
     */
    private $status = self::STATUS_PLAYED;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_orphaned", type="boolean")
     */
    private $isOrphaned = false;

    /**
     * @var PhaseGroup|null
     *
     * @ORM\ManyToOne(targetEntity="PhaseGroup", inversedBy="sets")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"profiles_sets"})
     */
    private $phaseGroup;

    /**
     * @var Entrant|null
     *
     * @ORM\ManyToOne(targetEntity="Entrant", inversedBy="entrantOneSets")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"profiles_sets"})
     */
    private $entrantOne;

    /**
     * @var Entrant|null
     *
     * @ORM\ManyToOne(targetEntity="Entrant", inversedBy="entrantTwoSets")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Serializer\Expose()
     * @Serializer\Groups({"profiles_sets"})
     */
    private $entrantTwo;

    /**
     * @var Entrant|null
     *
     * @ORM\ManyToOne(targetEntity="Entrant")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $winner;

    /**
     * @var Entrant|null
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
    public function __toString(): string
    {
        return 'Set #'.$this->getId();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * @param string|null $externalId
     */
    public function setExternalId(?string $externalId): void
    {
        $this->externalId = $externalId;
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
    public function setRound(int $round): void
    {
        $this->round = $round;
    }

    /**
     * @return string
     */
    public function getRoundName(): string
    {
        if ($this->phaseGroup instanceof PhaseGroup) {
            switch ($this->phaseGroup->getType()) {
                case PhaseGroup::TYPE_SWISS:
                    return 'Swiss';

                case PhaseGroup::TYPE_ROUND_ROBIN:
                    return 'Round Robin Pools';
            }
        }

        if ($this->roundName === null) {
            return 'Unknown round';
        }

        return $this->roundName;
    }

    /**
     * @param string|null $roundName
     */
    public function setRoundName(?string $roundName): void
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
    public function setIsFinals(bool $isFinals): void
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
    public function setIsGrandFinals(bool $isGrandFinals): void
    {
        $this->isGrandFinals = $isGrandFinals;
    }

    /**
     * @return int|null
     */
    public function getWinnerScore(): ?int
    {
        return $this->winnerScore;
    }

    /**
     * @param int|null $winnerScore
     */
    public function setWinnerScore(?int $winnerScore): void
    {
        $this->winnerScore = $winnerScore;
    }

    /**
     * @return int|null
     */
    public function getLoserScore(): ?int
    {
        return $this->loserScore;
    }

    /**
     * @param int|null $loserScore
     */
    public function setLoserScore(?int $loserScore): void
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
    public function setIsRanked(bool $isRanked): void
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
     * @return bool
     */
    public function wasNotPlayed(): bool
    {
        return $this->status === self::STATUS_NOT_PLAYED;
    }

    /**
     * @param string $status
     */
    public function setStatus(string $status): void
    {
        $this->status = $status;
    }

    /**
     * @return bool
     */
    public function isOrphaned(): bool
    {
        return $this->isOrphaned;
    }

    /**
     * @param bool $isOrphaned
     */
    public function setIsOrphaned(bool $isOrphaned): void
    {
        $this->isOrphaned = $isOrphaned;
    }

    /**
     * @return PhaseGroup|null
     */
    public function getPhaseGroup(): ?PhaseGroup
    {
        return $this->phaseGroup;
    }

    /**
     * @param PhaseGroup|null $phaseGroup
     */
    public function setPhaseGroup(?PhaseGroup $phaseGroup): void
    {
        $this->phaseGroup = $phaseGroup;
        $phaseGroup->addSet($this);
    }

    /**
     * @return Entrant|null
     */
    public function getEntrantOne(): ?Entrant
    {
        return $this->entrantOne;
    }

    /**
     * @param Entrant|null $entrantOne
     */
    public function setEntrantOne(?Entrant $entrantOne): void
    {
        $this->entrantOne = $entrantOne;
    }

    /**
     * @return string
     */
    public function getEntrantOneName(): string
    {
        if ($this->entrantOne instanceof Entrant) {
            return $this->entrantOne->getName();
        }

        return 'bye';
    }

    /**
     * @return int
     */
    public function getEntrantOneScore(): int
    {
        if ($this->entrantOne instanceof Entrant && $this->entrantOne === $this->winner) {
            return $this->getWinnerScore();
        }

        return $this->getLoserScore();
    }

    /**
     * @return Entrant|null
     */
    public function getEntrantTwo(): ?Entrant
    {
        return $this->entrantTwo;
    }

    /**
     * @param Entrant|null $entrantTwo
     */
    public function setEntrantTwo(?Entrant $entrantTwo): void
    {
        $this->entrantTwo = $entrantTwo;
    }

    /**
     * @return string
     */
    public function getEntrantTwoName(): string
    {
        if ($this->entrantTwo instanceof Entrant) {
            return $this->entrantTwo->getName();
        }

        return 'bye';
    }

    /**
     * @return int
     */
    public function getEntrantTwoScore(): int
    {
        if ($this->entrantTwo instanceof Entrant && $this->entrantTwo === $this->winner) {
            return $this->getWinnerScore();
        }

        return $this->getLoserScore();
    }

    /**
     * @return bool
     */
    public function hasResult(): bool
    {
        return $this->winner instanceof Entrant && $this->loser instanceof Entrant;
    }

    /**
     * @return bool
     */
    public function hasResultWithScore(): bool
    {
        return $this->hasResult() && $this->winnerScore !== null && $this->loserScore !== null;
    }

    /**
     * @param bool $reverse
     *
     * @return string|null
     */
    public function getTag($reverse = false): ?string
    {
        if ($this->entrantOne instanceof Entrant && $this->entrantTwo instanceof Entrant) {
            if ($reverse) {
                return $this->entrantTwo->getId().'-'.$this->entrantOne->getId();
            }

            return $this->entrantOne->getId().'-'.$this->entrantTwo->getId();
        }

        return null;
    }

    /**
     * @param Entrant $entrant
     *
     * @return bool
     */
    public function hasEntrant(Entrant $entrant): bool
    {
        return $entrant === $this->entrantOne || $entrant === $this->entrantTwo;
    }

    /**
     * @return Entrant|null
     */
    public function getWinner(): ?Entrant
    {
        return $this->winner;
    }

    /**
     * @return int|null
     *
     * @Serializer\Groups({"profiles_sets"})
     * @Serializer\SerializedName("winner")
     * @Serializer\VirtualProperty()
     */
    public function getWinnerId(): ?int
    {
        if ($this->winner instanceof Entrant) {
            return $this->winner->getId();
        }

        return null;
    }

    /**
     * @param Entrant|null $winner
     */
    public function setWinner(?Entrant $winner = null): void
    {
        $this->winner = $winner;
    }

    /**
     * @param Entrant $entrant
     *
     * @return bool
     */
    public function isWinner(Entrant $entrant): bool
    {
        return $entrant === $this->winner;
    }

    /**
     * @return Entrant|null
     */
    public function getLoser(): ?Entrant
    {
        return $this->loser;
    }

    /**
     * @return int|null
     *
     * @Serializer\Groups({"profiles_sets"})
     * @Serializer\SerializedName("loser")
     * @Serializer\VirtualProperty()
     */
    public function getLoserId(): ?int
    {
        if ($this->loser instanceof Entrant) {
            return $this->loser->getId();
        }

        return null;
    }

    /**
     * @param Entrant|null $loser
     */
    public function setLoser(?Entrant $loser = null): void
    {
        $this->loser = $loser;
    }

    /**
     * @param Entrant $entrant
     *
     * @return bool
     */
    public function isLoser(Entrant $entrant): bool
    {
        return $entrant === $this->loser;
    }

    /**
     * @return int
     */
    public function getLoserRank(): int
    {
        return $this->loserRank;
    }

    /**
     * @param int $loserRank
     */
    public function setLoserRank(int $loserRank): void
    {
        $this->loserRank = $loserRank;
    }
}
