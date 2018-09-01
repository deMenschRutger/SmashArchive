<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="profile", indexes={
 *     @ORM\Index(name="slug_index", columns={"slug"}),
 *     @ORM\Index(name="gamer_tag_index", columns={"gamer_tag"}),
 *     @ORM\Index(name="name_index", columns={"name"}),
 *     @ORM\Index(name="region_index", columns={"region"}),
 *     @ORM\Index(name="city_index", columns={"city"}),
 *     @ORM\Index(name="is_competing_index", columns={"is_competing"}),
 *     @ORM\Index(name="is_active_index", columns={"is_active"}),
 *     @ORM\Index(name="created_at_index", columns={"created_at"}),
 *     @ORM\Index(name="updated_at_index", columns={"updated_at"}),
 * })
 * @ORM\Entity(repositoryClass="App\Repository\PlayerProfileRepository")
 */
class Profile
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"players_overview"})
     */
    private $id;

    /**
     * @var string
     *
     * @Gedmo\Slug(fields={"gamerTag"}, updatable=false)
     * @ORM\Column(name="slug", type="string", length=128, unique=true)
     *
     * @Serializer\Groups({"players_overview", "tournaments_overview", "tournaments_details"})
     */
    private $slug;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"players_overview", "tournaments_details"})
     *
     * @TODO The serializer doesn't serialize null values.
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="gamer_tag", type="string")
     *
     * @Serializer\Groups({"players_overview", "tournaments_overview", "tournaments_details"})
     */
    private $gamerTag;

    /**
     * @var Country|null
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="playersNationalities")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Serializer\Groups({"players_overview", "tournaments_details"})
     */
    private $nationality;

    /**
     * @var Country|null
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="playersCountries")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Serializer\Groups({"players_overview", "tournaments_details"})
     */
    private $country;

    /**
     * @var string|null
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"players_overview", "tournaments_details"})
     */
    private $region;

    /**
     * @var string|null
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"players_overview", "tournaments_details"})
     */
    private $city;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_competing", type="boolean")
     *
     * @Serializer\Groups({"players_overview"})
     */
    private $isCompeting = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_active", type="boolean")
     *
     * @Serializer\Groups({"players_overview"})
     */
    private $isActive = true;

    /**
     * @var array
     *
     * @ORM\Column(name="properties", type="json_array")
     */
    private $properties = [];

    /**
     * @var Character[]
     *
     * @ORM\ManyToMany(targetEntity="Character")
     * @ORM\JoinTable(
     *  name="players_mains",
     *  joinColumns={
     *     @ORM\JoinColumn(name="player_profile_id", referencedColumnName="id", onDelete="CASCADE")
     *  },
     *  inverseJoinColumns={
     *     @ORM\JoinColumn(name="character_id", referencedColumnName="id", onDelete="CASCADE")
     *  }
     * )
     *
     * @Serializer\Groups({"players_overview"})
     */
    private $mains;

    /**
     * @var Character[]
     *
     * @ORM\ManyToMany(targetEntity="Character")
     * @ORM\JoinTable(
     *  name="players_secondaries",
     *  joinColumns={
     *     @ORM\JoinColumn(name="player_profile_id", referencedColumnName="id", onDelete="CASCADE")
     *  },
     *  inverseJoinColumns={
     *     @ORM\JoinColumn(name="character_id", referencedColumnName="id", onDelete="CASCADE")
     *  }
     * )
     *
     * @Serializer\Groups({"players_overview"})
     */
    private $secondaries;

    /**
     * @var Tournament[]
     *
     * @ORM\ManyToMany(targetEntity="Tournament", mappedBy="organizers")
     */
    private $tournamentsOrganized;

    /**
     * @var Player[]
     *
     * @ORM\OneToMany(targetEntity="Player", mappedBy="profile")
     */
    private $players;

    /**
     *
     */
    public function __construct()
    {
        $this->mains = new ArrayCollection();
        $this->secondaries = new ArrayCollection();
        $this->tournamentsOrganized = new ArrayCollection();
        $this->players = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getSlug(): string
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug(string $slug): void
    {
        $this->slug = $slug;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getGamerTag(): string
    {
        return $this->gamerTag;
    }

    /**
     * @param string $gamerTag
     */
    public function setGamerTag(string $gamerTag): void
    {
        $this->gamerTag = $gamerTag;
    }

    /**
     * @return string
     */
    public function getExpandedGamerTag(): string
    {
        $location = $this->getLocation();

        if (!$location) {
            $location = 'unknown';
        }

        return sprintf('%s | %s | %s | #%s', $this->getGamerTag(), $location, $this->getSlug(), $this->getId());
    }

    /**
     * @return Country|null
     */
    public function getNationality(): ?Country
    {
        return $this->nationality;
    }

    /**
     * @param Country|null $nationality
     */
    public function setNationality(?Country $nationality): void
    {
        $this->nationality = $nationality;
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
     * @return string
     *
     * @Serializer\Groups({"players_overview"})
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
     * @return bool
     */
    public function getIsCompeting(): bool
    {
        return $this->isCompeting;
    }

    /**
     * @param bool $isCompeting
     */
    public function setIsCompeting(bool $isCompeting): void
    {
        $this->isCompeting = $isCompeting;
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
     * @param string $key
     *
     * @return mixed
     */
    public function getProperty(string $key)
    {
        if (!array_key_exists($key, $this->properties)) {
            return null;
        }

        return $this->properties[$key];
    }

    /**
     * @param string $key
     * @param mixed  $value
     */
    public function setProperty(string $key, $value): void
    {
        $this->properties[$key] = $value;
    }

    /**
     * @return Character[]|Collection
     */
    public function getMains(): Collection
    {
        return $this->mains;
    }

    /**
     * @param Character $character
     */
    public function addMain(Character $character): void
    {
        $this->mains[] = $character;
    }

    /**
     * @return Character[]|Collection
     */
    public function getSecondaries(): Collection
    {
        return $this->secondaries;
    }

    /**
     * @param Character $character
     */
    public function addSecondary(Character $character): void
    {
        $this->secondaries[] = $character;
    }
}
