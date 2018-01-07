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
 * @ORM\Table(name="player_profile", indexes={
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
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\PlayerProfileRepository")
 */
class PlayerProfile
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
     * @Serializer\Groups({"players_overview", "tournaments_results", "tournaments_overview", "tournaments_details"})
     */
    private $slug;

    /**
     * @var string
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
     * @Serializer\Groups({"players_overview", "tournaments_results", "tournaments_overview", "tournaments_details"})
     */
    private $gamerTag;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="playersNationalities")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Serializer\Groups({"players_overview", "tournaments_details"})
     */
    private $nationality;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="playersCountries")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Serializer\Groups({"players_overview", "tournaments_details"})
     */
    private $country;

    /**
     * @var string
     *
     * @ORM\Column(name="region", type="string", length=255, nullable=true)
     *
     * @Serializer\Groups({"players_overview", "tournaments_details"})
     */
    private $region;

    /**
     * @var string
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
     *
     * @Serializer\Groups({"players_overview"})
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
     *
     * @Serializer\Groups({"players_overview"})
     */
    private $secondaries;

    /**
     * @ORM\ManyToMany(targetEntity="Tournament", mappedBy="organizers")
     */
    private $tournamentsOrganized;

    /**
     * @ORM\OneToMany(targetEntity="Player", mappedBy="playerProfile")
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
}
