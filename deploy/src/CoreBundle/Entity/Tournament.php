<?php

declare(strict_types=1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="tournament")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\TournamentRepository")
 */
class Tournament
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"players_results", "tournaments_overview", "tournaments_details"})
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
     * @Gedmo\Slug(fields={"name"})
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     *
     * @Serializer\Groups({"players_results", "tournaments_overview", "tournaments_details"})
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="smashgg_slug", type="string", length=255, nullable=true)
     */
    private $smashggSlug;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Serializer\Groups({"players_results", "tournaments_overview", "tournaments_details"})
     */
    private $name;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="tournaments")
     *
     * @Serializer\Groups({"tournaments_overview"})
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="results_page", type="text", nullable=true)
     */
    private $resultsPage;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_complete", type="boolean")
     *
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $isComplete;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="Event", mappedBy="tournament")
     *
     * @Serializer\Groups({"tournaments_details"})
     */
    private $events;

    /**
     *
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
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
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @return string
     */
    public function getSmashggSlug()
    {
        return $this->smashggSlug;
    }

    /**
     * @param string $smashggSlug
     */
    public function setSmashggSlug($smashggSlug)
    {
        $this->smashggSlug = $smashggSlug;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return Country
     */
    public function getCountry(): Country
    {
        return $this->country;
    }

    /**
     * @param Country $country
     */
    public function setCountry(Country $country)
    {
        $this->country = $country;
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
     * @return bool
     */
    public function getIsComplete()
    {
        return $this->isComplete;
    }

    /**
     * @param bool $isComplete
     */
    public function setIsComplete($isComplete)
    {
        $this->isComplete = $isComplete;
    }

    /**
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return Collection
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }
}

