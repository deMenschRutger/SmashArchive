<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="phase_group", indexes={
 *     @ORM\Index(name="external_id_index", columns={"external_id"}),
 *     @ORM\Index(name="name_index", columns={"name"}),
 *     @ORM\Index(name="type_index", columns={"type"}),
 *     @ORM\Index(name="created_at_index", columns={"created_at"}),
 *     @ORM\Index(name="updated_at_index", columns={"updated_at"}),
 * })
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\PhaseGroupRepository")
 */
class PhaseGroup
{
    use TimestampableTrait;

    const TYPE_SINGLE_ELIMINATION = 1;
    const TYPE_DOUBLE_ELIMINATION = 2;
    const TYPE_ROUND_ROBIN        = 3;
    const TYPE_SWISS              = 4;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"players_sets", "tournaments_details"})
     */
    private $id;

    /**
     * @var string|null
     *
     * @ORM\Column(name="external_id", type="string", length=255, nullable=true)
     */
    private $externalId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Serializer\Groups({"players_sets", "tournaments_details"})
     */
    private $name;

    /**
     * @var string|null
     *
     * @ORM\Column(name="results_page", type="text", nullable=true)
     *
     * @Serializer\Groups({"tournaments_details"})
     */
    private $resultsPage;

    /**
     * @var string|null
     *
     * @ORM\Column(name="smash_ranking_info", type="text", nullable=true)
     */
    private $smashRankingInfo;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="smallint")
     *
     * @Serializer\Groups({"tournaments_details"})
     */
    private $type;

    /**
     * @var Phase|null
     *
     * @ORM\ManyToOne(targetEntity="Phase", inversedBy="phaseGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $phase;

    /**
     * @var Set[]
     *
     * @ORM\OneToMany(targetEntity="Set", mappedBy="phaseGroup")
     */
    private $sets;

    /**
     *
     */
    public function __construct()
    {
        $this->sets = new ArrayCollection();
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
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getExtendedName(): string
    {
        $phase = $this->getPhase();

        if (count($phase->getPhaseGroups()) > 1) {
            return sprintf('%s - %s', $phase->getName(), $this->getName());
        }

        return $phase->getName();
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string|null
     */
    public function getResultsPage(): ?string
    {
        return $this->resultsPage;
    }

    /**
     * @param string|null $resultsPage
     */
    public function setResultsPage(?string $resultsPage): void
    {
        $this->resultsPage = $resultsPage;
    }

    /**
     * @return string|null
     */
    public function getSmashRankingInfo(): ?string
    {
        return $this->smashRankingInfo;
    }

    /**
     * @param string|null $smashRankingInfo
     */
    public function setSmashRankingInfo(?string $smashRankingInfo): void
    {
        $this->smashRankingInfo = $smashRankingInfo;
    }

    /**
     * @return int
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * @param int $type
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }

    /**
     * @return Phase|null
     */
    public function getPhase(): ?Phase
    {
        return $this->phase;
    }

    /**
     * @param Phase|null $phase
     */
    public function setPhase(?Phase $phase): void
    {
        $this->phase = $phase;
    }

    /**
     * @return Set[]|ArrayCollection
     */
    public function getSets(): array
    {
        return $this->sets;
    }

    /**
     * @param Set $set
     */
    public function addSet(Set $set): void
    {
        $this->sets[] = $set;
    }

    /**
     * @return Entrant[]
     */
    public function getEntrants(): array
    {
        $entrants = [];

        /** @var Set $set */
        foreach ($this->getSets() as $set) {
            $entrants[] = $set->getEntrantOne();
            $entrants[] = $set->getEntrantTwo();
        }

        return array_unique(array_filter($entrants));
    }

    /**
     * @return Player[]
     */
    public function getPlayers(): array
    {
        $players = [];

        foreach ($this->getEntrants() as $entrant) {
            foreach ($entrant->getPlayers() as $player) {
                $players[] = $player;
            }
        }

        return array_unique($players);
    }
}
