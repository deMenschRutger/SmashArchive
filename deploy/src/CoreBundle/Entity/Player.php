<?php

declare(strict_types = 1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="player", indexes={
 *     @ORM\Index(name="smashgg_index", columns={"smashgg_id"}),
 *     @ORM\Index(name="slug_index", columns={"slug"}),
 *     @ORM\Index(name="gamer_tag_index", columns={"gamer_tag"}),
 *     @ORM\Index(name="name_index", columns={"name"}),
 *     @ORM\Index(name="region_index", columns={"region"}),
 *     @ORM\Index(name="city_index", columns={"city"}),
 *     @ORM\Index(name="is_competing_index", columns={"is_competing"}),
 *     @ORM\Index(name="is_active_index", columns={"is_active"}),
 *     @ORM\Index(name="is_new_index", columns={"is_new"}),
 *     @ORM\Index(name="created_at_index", columns={"created_at"}),
 *     @ORM\Index(name="updated_at_index", columns={"updated_at"}),
 * })
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\PlayerRepository")
 */
class Player
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
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     *
     * @Serializer\Groups({"players_overview", "tournaments_results"})
     */
    private $slug;

    /**
     * @var int
     *
     * @ORM\Column(name="smash_ranking_id", type="integer", nullable=true)
     */
    private $smashRankingId;

    /**
     * @var string
     *
     * @ORM\Column(name="smashgg_id", type="integer", nullable=true)
     */
    private $smashggId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"players_overview"})
     *
     * @TODO The serializer doesn't serialize null values.
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="gamer_tag", type="string")
     *
     * @Serializer\Groups({"players_overview", "tournaments_results"})
     */
    private $gamerTag;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="playersNationalities")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Serializer\Groups({"players_overview"})
     */
    private $nationality;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="playersCountries")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Serializer\Groups({"players_overview"})
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"players_overview"})
     */
    private $region;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"players_overview"})
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
     * @var bool
     *
     * @ORM\Column(name="is_new", type="boolean")
     */
    private $isNew = true;

    /**
     * @var array
     *
     * @ORM\Column(name="properties", type="json_array")
     */
    private $properties = [];

    /**
     * @ORM\ManyToMany(targetEntity="Character")
     * @ORM\JoinTable(
     *  name="players_mains",
     *  joinColumns={
     *     @ORM\JoinColumn(name="player_id", referencedColumnName="id", onDelete="CASCADE")
     *  },
     *  inverseJoinColumns={
     *     @ORM\JoinColumn(name="character_id", referencedColumnName="id", onDelete="CASCADE")
     *  }
     * )
     */
    private $mains;

    /**
     * @ORM\ManyToMany(targetEntity="Character")
     * @ORM\JoinTable(
     *  name="players_secondaries",
     *  joinColumns={
     *     @ORM\JoinColumn(name="player_id", referencedColumnName="id", onDelete="CASCADE")
     *  },
     *  inverseJoinColumns={
     *     @ORM\JoinColumn(name="character_id", referencedColumnName="id", onDelete="CASCADE")
     *  }
     * )
     */
    private $secondaries;

    /**
     * @ORM\ManyToMany(targetEntity="Entrant", mappedBy="players")
     */
    private $entrants;

    /**
     * @ORM\ManyToMany(targetEntity="Tournament", mappedBy="organizers")
     */
    private $tournamentsOrganized;

    /**
     * The tournament that resulted in the player becoming a part of the database.
     *
     * @ORM\ManyToOne(targetEntity="Tournament")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $originTournament;

    /**
     * Used for merging two players.
     *
     * @var Player
     *
     * @ORM\OneToOne(targetEntity="Player")
     */
    private $targetPlayer;

    /**
     *
     */
    public function __construct()
    {
        $this->mains = new ArrayCollection();
        $this->secondaries = new ArrayCollection();
        $this->entrants = new ArrayCollection();
        $this->tournamentsOrganized = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getGamerTag();
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
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @param string $slug
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    }

    /**
     * @return string
     */
    public function getResultsCacheTag()
    {
        return 'player_results_'.$this->slug;
    }

    /**
     * @return string
     */
    public function getCacheTag()
    {
        return 'player_'.$this->slug;
    }

    /**
     * @return int
     */
    public function getSmashRankingId()
    {
        return $this->smashRankingId;
    }

    /**
     * @param int $smashRankingId
     */
    public function setSmashRankingId($smashRankingId)
    {
        $this->smashRankingId = $smashRankingId;
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
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getGamerTag()
    {
        return $this->gamerTag;
    }

    /**
     * @param string $gamerTag
     */
    public function setGamerTag(string $gamerTag)
    {
        $this->gamerTag = $gamerTag;
    }

    /**
     * @return string
     */
    public function getExpandedGamerTag()
    {
        $location = $this->getLocation();

        if (!$location) {
            $location = 'unknown';
        }

        return sprintf('%s | %s | %s | #%s', $this->getGamerTag(), $location, $this->getSlug(), $this->getId());
    }

    /**
     * @return Country
     */
    public function getNationality()
    {
        return $this->nationality;
    }

    /**
     * @param Country $nationality
     */
    public function setNationality($nationality)
    {
        $this->nationality = $nationality;
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
    public function setCountry($country)
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
     * @return string
     *
     * @Serializer\Groups({"players_overview"})
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
     * @return bool
     */
    public function getIsCompeting()
    {
        return $this->isCompeting;
    }

    /**
     * @param bool $isCompeting
     */
    public function setIsCompeting($isCompeting)
    {
        $this->isCompeting = $isCompeting;
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
     * @return bool
     */
    public function isNew()
    {
        return $this->isNew;
    }

    /**
     * @param bool $isNew
     */
    public function setIsNew(bool $isNew)
    {
        $this->isNew = $isNew;
    }

    /**
     * @param string $key
     * @return array
     */
    public function getProperty($key)
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
    public function setProperty($key, $value)
    {
        $this->properties[$key] = $value;
    }

    /**
     * @return Collection
     */
    public function getMains(): Collection
    {
        return $this->mains;
    }

    /**
     * @param Character $character
     */
    public function addMain(Character $character)
    {
        $this->mains[] = $character;
    }

    /**
     * @return Collection
     */
    public function getSecondaries(): Collection
    {
        return $this->secondaries;
    }

    /**
     * @param Character $character
     */
    public function addSecondary(Character $character)
    {
        $this->secondaries[] = $character;
    }

    /**
     * @return Collection
     */
    public function getEntrants(): Collection
    {
        return $this->entrants;
    }

    /**
     * @param Entrant $entrant
     */
    public function addEntrant(Entrant $entrant)
    {
        $this->entrants[] = $entrant;
    }

    /**
     * @return Tournament
     */
    public function getOriginTournament()
    {
        return $this->originTournament;
    }

    /**
     * @param Tournament $originTournament
     */
    public function setOriginTournament(Tournament $originTournament)
    {
        $this->originTournament = $originTournament;
    }

    /**
     * @return Player
     */
    public function getTargetPlayer()
    {
        return $this->targetPlayer;
    }

    /**
     * @param Player $targetPlayer
     */
    public function setTargetPlayer($targetPlayer)
    {
        $this->targetPlayer = $targetPlayer;
    }
}
