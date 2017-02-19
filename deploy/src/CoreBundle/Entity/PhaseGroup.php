<?php

declare(strict_types=1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="phase_group")
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
     */
    private $id;

    /**
     * The original event ID from the SmashRanking database.
     *
     * @var int
     *
     * @ORM\Column(name="original_id", type="integer")
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
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="results_url", type="text", nullable=true)
     */
    private $resultsUrl;

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
     */
    private $type;

    /**
     * @ORM\ManyToOne(targetEntity="Phase", inversedBy="phaseGroups")
     */
    private $phase;

    /**
     * @ORM\OneToMany(targetEntity="Set", mappedBy="phaseGroup")
     */
    private $sets;

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
    public function getResultsUrl()
    {
        return $this->resultsUrl;
    }

    /**
     * @param string $resultsUrl
     */
    public function setResultsUrl($resultsUrl)
    {
        $this->resultsUrl = $resultsUrl;
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
}

