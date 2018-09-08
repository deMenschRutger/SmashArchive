<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
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
 * @ORM\Entity(repositoryClass="App\Repository\TournamentRepository")
 *
 * @Serializer\ExclusionPolicy("all")
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
     * @Serializer\Expose
     * @Serializer\Groups({"players_sets", "tournaments_overview", "tournaments_details"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", length=255)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $source = self::SOURCE_CUSTOM;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"name"}, updatable=false)
     * @ORM\Column(name="slug", type="string", length=128, unique=true)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"players_sets", "tournaments_overview", "tournaments_details"})
     */
    private $slug;

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
     * @Serializer\Expose
     * @Serializer\Groups({"players_sets", "tournaments_overview", "tournaments_details"})
     */
    private $name;

    /**
     * @var Country|null
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="tournaments")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $country;

    /**
     * @var string|null
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $region;

    /**
     * @var string|null
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $city;

    /**
     * The first (and possibly only) day of the tournament
     *
     * @var \DateTime|null
     *
     * @ORM\Column(name="date_start", type="date", nullable=true)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $dateStart;

    /**
     * @var string|null
     *
     * @ORM\Column(name="smashgg_url", type="text", nullable=true)
     * @Assert\Url
     */
    private $smashggUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(name="facebook_event_url", type="text", nullable=true)
     * @Assert\Url
     */
    private $facebookEventUrl;

    /**
     * @var string|null
     *
     * @ORM\Column(name="results_page", type="text", nullable=true)
     * @Assert\Url
     */
    private $resultsPage;

    /**
     * @var int|null
     *
     * @ORM\Column(name="player_count", type="integer", nullable=true)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $playerCount;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_complete", type="boolean")
     *
     * @Serializer\Expose
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
     * @var Series|null
     *
     * @ORM\ManyToOne(targetEntity="Series", inversedBy="tournaments")
     */
    private $series;

    /**
     * @var Player[]
     *
     * @ORM\ManyToMany(targetEntity="Profile", inversedBy="tournamentsOrganized")
     * @ORM\JoinTable(name="tournaments_organizers")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $organizers;

    /**
     * @var Event[]
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="tournament", cascade={"persist"}, orphanRemoval=true)
     *
     * @Serializer\Expose
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
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource(string $source): void
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
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
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Country|null
     */
    public function getCountry(): ?Country
    {
        return $this->country;
    }

    /**
     * @param Country|null $country
     */
    public function setCountry(?Country $country): void
    {
        $this->country = $country;
    }

    /**
     * @return string|null
     */
    public function getRegion(): ?string
    {
        return $this->region;
    }

    /**
     * @param string|null $region
     */
    public function setRegion(?string $region): void
    {
        $this->region = $region;
    }

    /**
     * @return string|null
     */
    public function getCity(): ?string
    {
        return $this->city;
    }

    /**
     * @param string|null $city
     */
    public function setCity(?string $city): void
    {
        $this->city = $city;
    }

    /**
     * @param string|null $format
     *
     * @return \DateTime|string|null
     */
    public function getDateStart($format = null)
    {
        if ($this->dateStart instanceof \DateTime && $format) {
            return $this->dateStart->format($format);
        }

        return $this->dateStart;
    }

    /**
     * @param \DateTime|null $dateStart
     */
    public function setDateStart(?\DateTime $dateStart): void
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
    public function getLocation(): string
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
     * @return int|null
     */
    public function getPlayerCount(): ?int
    {
        return $this->playerCount;
    }

    /**
     * @return void
     */
    public function setPlayerCount(): void
    {
        $this->playerCount = count($this->getPlayers());
    }

    /**
     * @return string|null
     */
    public function getSmashggUrl(): ?string
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
     * @param string|null $smashggUrl
     */
    public function setSmashggUrl(?string $smashggUrl): void
    {
        $this->smashggUrl = $smashggUrl;
    }

    /**
     * @return string|null
     */
    public function getFacebookEventUrl(): ?string
    {
        return $this->facebookEventUrl;
    }

    /**
     * @param string|null $facebookEventUrl
     */
    public function setFacebookEventUrl(?string $facebookEventUrl): void
    {
        $this->facebookEventUrl = $facebookEventUrl;
    }

    /**
     * @return bool
     */
    public function getIsComplete(): bool
    {
        return $this->isComplete;
    }

    /**
     * @param bool $isComplete
     */
    public function setIsComplete(bool $isComplete): void
    {
        $this->isComplete = $isComplete;
    }

    /**
     * @return bool
     */
    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    /**
     * @param bool $isActive
     */
    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    /**
     * @return Series|null
     */
    public function getSeries(): ?Series
    {
        return $this->series;
    }

    /**
     * @param Series $series
     */
    public function setSeries(?Series $series): void
    {
        $this->series = $series;
    }

    /**
     * @return Profile[]|Collection
     */
    public function getOrganizers(): Collection
    {
        return $this->organizers;
    }

    /**
     * @param Profile $organizer
     */
    public function addOrganizer(Profile $organizer): void
    {
        $this->organizers[] = $organizer;
    }

    /**
     * @param Profile[] $organizers
     */
    public function setOrganizers(array $organizers): void
    {
        $this->organizers = new ArrayCollection();

        foreach ($organizers as $organizer) {
            $this->addOrganizer($organizer);
        }
    }

    /**
     * @return Event[]|Collection
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }

    /**
     * @param Event $event
     */
    public function addEvent(Event $event): void
    {
        $this->events->add($event);
        $event->setTournament($this);
    }

    /**
     * @param Event[] $events
     */
    public function setEvents(array $events): void
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
