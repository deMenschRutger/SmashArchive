<?php

declare(strict_types = 1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="phase_group", indexes={
 *     @ORM\Index(name="smashgg_index", columns={"smashgg_id"}),
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
     * The original event ID from the SmashRanking database.
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
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Serializer\Groups({"players_sets", "tournaments_details"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="results_page", type="text", nullable=true)
     *
     * @Serializer\Groups({"tournaments_details"})
     */
    private $resultsPage;

    /**
     * @var string
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
     * @ORM\ManyToOne(targetEntity="Phase", inversedBy="phaseGroups")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $phase;

    /**
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
     * @return string
     */
    public function getResultsPage()
    {
        return $this->resultsPage;
    }

    /**
     * @param string $resultsPage
     */
    public function setResultsPage($resultsPage)
    {
        $this->resultsPage = $resultsPage;
    }

    /**
     * @return string
     */
    public function getSmashRankingInfo()
    {
        return $this->smashRankingInfo;
    }

    /**
     * @param string $smashRankingInfo
     */
    public function setSmashRankingInfo($smashRankingInfo)
    {
        $this->smashRankingInfo = $smashRankingInfo;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
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
    public function setType(int $type)
    {
        $this->type = $type;
    }

    /**
     * @return Phase
     */
    public function getPhase(): Phase
    {
        return $this->phase;
    }

    /**
     * @param Phase $phase
     */
    public function setPhase(Phase $phase)
    {
        $this->phase = $phase;
    }

    /**
     * @return Collection
     */
    public function getSets(): Collection
    {
        return $this->sets;
    }

    /**
     * @param Set $set
     */
    public function addSet(Set $set)
    {
        $this->sets[] = $set;
    }

    /**
     * @return array
     */
    public function getEntrants()
    {
        $entrants = [];

        /** @var Set $set */
        foreach ($this->getSets() as $set) {
            $entrants[] = $set->getEntrantOne();
            $entrants[] = $set->getEntrantTwo();
        }

        return array_unique(array_filter($entrants));
    }
}
