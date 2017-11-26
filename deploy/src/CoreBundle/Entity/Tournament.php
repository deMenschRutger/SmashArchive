<?php

declare(strict_types = 1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Table(name="tournament", indexes={
 *     @ORM\Index(name="external_id_index", columns={"external_id"}),
 *     @ORM\Index(name="name_index", columns={"name"}),
 *     @ORM\Index(name="region_index", columns={"region"}),
 *     @ORM\Index(name="city_index", columns={"city"}),
 *     @ORM\Index(name="date_start_index", columns={"date_start"}),
 *     @ORM\Index(name="is_active_index", columns={"is_active"})
 * })
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\TournamentRepository")
 */
class Tournament
{
    const SOURCE_CUSTOM       = 'custom';
    const SOURCE_SMASHGG      = 'smashgg';
    const SOURCE_CHALLONGE    = 'challonge';
    const SOURCE_TIO          = 'tio';
    const SOURCE_SMASHRANKING = 'smashranking';

    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"players_sets", "tournaments_overview", "tournaments_details"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=255)
     *
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $source = self::SOURCE_CUSTOM;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     *
     * @Serializer\Groups({"players_sets", "tournaments_overview", "tournaments_details"})
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="external_id", type="string", length=255, nullable=true)
     */
    private $externalId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Serializer\Groups({"players_sets", "tournaments_overview", "tournaments_details"})
     */
    private $name;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="tournaments")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $city;

    /**
     * The first (and possibly only) day of the tournament
     *
     * @var \DateTime
     *
     * @ORM\Column(name="date_start", type="date", nullable=true)
     *
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $dateStart;

    /**
     * @var string
     *
     * @ORM\Column(name="smashgg_url", type="text", nullable=true)
     * @Assert\Url
     */
    private $smashggUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_event_url", type="text", nullable=true)
     * @Assert\Url
     */
    private $facebookEventUrl;

    /**
     * @var string
     *
     * @ORM\Column(name="results_page", type="text", nullable=true)
     * @Assert\Url
     */
    private $resultsPage;

    /**
     * @var int
     *
     * @ORM\Column(name="player_count", type="integer", nullable=true)
     *
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $playerCount;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_complete", type="boolean")
     *
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $isComplete = false;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @var Series
     *
     * @ORM\ManyToOne(targetEntity="Series", inversedBy="tournaments")
     */
    private $series;

    /**
     * @var Player[]
     *
     * @ORM\ManyToMany(targetEntity="Player", inversedBy="tournamentsOrganized")
     * @ORM\JoinTable(name="tournaments_organizers")
     *
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $organizers;

    /**
     * @var Event[]
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="tournament", cascade={"persist"}, orphanRemoval=true)
     *
     * @Serializer\Groups({"tournaments_details"})
     */
    private $events;

    /**
     *
     */
    public function __construct()
    {
        $this->organizers = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getName();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source)
    {
        $this->source = $source;
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
    public function getExternalId()
    {
        return $this->externalId;
    }

    /**
     * @param string $externalId
     */
    public function setExternalId($externalId)
    {
        $this->externalId = $externalId;
    }

    /**
     * @return string
     */
    public function getName()
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
    public function getCountry()
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
    public function getRegion()
    {
        return $this->region;
    }

    /**
     * @param string $region
     */
    public function setRegion($region)
    {
        $this->region = $region;
    }

    /**
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * @param string $city
     */
    public function setCity($city)
    {
        $this->city = $city;
    }

    /**
     * @param string $format
     * @return \DateTime|string
     */
    public function getDateStart($format = null)
    {
        if ($this->dateStart instanceof \DateTime && $format) {
            return $this->dateStart->format($format);
        }

        return $this->dateStart;
    }

    /**
     * @param \DateTime $dateStart
     */
    public function setDateStart($dateStart)
    {
        $this->dateStart = $dateStart;
    }

    /**
     * @return string
     *
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     * @Serializer\SerializedName("location")
     * @Serializer\VirtualProperty()
     */
    public function getLocation()
    {
        $location = [
            $this->getCity(),
            $this->getRegion(),
        ];

        if ($this->country instanceof Country) {
            $location[] = $this->getCountry()->getName();
        }

        return join(', ', array_filter($location));
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
     * @return int
     */
    public function getPlayerCount()
    {
        return $this->playerCount;
    }

    /**
     * @return void
     */
    public function setPlayerCount()
    {
        $this->playerCount = count($this->getPlayers());
    }

    /**
     * @return string
     */
    public function getSmashggUrl()
    {
        return $this->smashggUrl;
    }

    /**
     * @return bool|int
     */
    public function getSmashggIdFromUrl()
    {
        preg_match('~https?:\/\/smash\.gg\/tournament\/([0-9a-z-]+)\/~', $this->getSmashggUrl(), $matches);

        if (!array_key_exists(1, $matches)) {
            return false;
        }

        return $matches[1];
    }

    /**
     * @param string $smashggUrl
     */
    public function setSmashggUrl($smashggUrl)
    {
        $this->smashggUrl = $smashggUrl;
    }

    /**
     * @return string
     */
    public function getFacebookEventUrl()
    {
        return $this->facebookEventUrl;
    }

    /**
     * @param string $facebookEventUrl
     */
    public function setFacebookEventUrl($facebookEventUrl)
    {
        $this->facebookEventUrl = $facebookEventUrl;
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
     * @return Series
     */
    public function getSeries()
    {
        return $this->series;
    }

    /**
     * @param Series $series
     */
    public function setSeries($series)
    {
        $this->series = $series;
    }

    /**
     * @return Collection|Player[]
     */
    public function getOrganizers(): Collection
    {
        return $this->organizers;
    }

    /**
     * @param Player $organizer
     */
    public function addOrganizer(Player $organizer)
    {
        $this->organizers[] = $organizer;
    }

    /**
     * @param ArrayCollection $organizers
     */
    public function setOrganizers(ArrayCollection $organizers)
    {
        $this->organizers = new ArrayCollection();

        foreach ($organizers as $organizer) {
            $this->addOrganizer($organizer);
        }
    }

    /**
     * @return Collection|Event[]
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param Event $event
     */
    public function addEvent(Event $event)
    {
        $this->events->add($event);
        $event->setTournament($this);
    }

    /**
     * @param Event[] $events
     */
    public function setEvents($events)
    {
        foreach ($events as $event) {
            $this->addEvent($event);
        }
    }

    /**
     * @return Player[]
     */
    public function getPlayers(): array
    {
        $players = [];

        foreach ($this->getEvents() as $event) {
            $players = array_merge($players, $event->getPlayers());
        }

        return array_unique($players);
    }
}
