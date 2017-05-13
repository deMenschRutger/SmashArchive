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
 * @ORM\Table(name="player")
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
     * @Gedmo\Slug(fields={"gamerTag"})
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     *
     * @Serializer\Groups({"players_overview", "tournaments_results"})
     */
    private $slug;

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
     *
     * @Serializer\Groups({"players_overview"})
     */
    private $nationality;

    /**
     * @var Country
     *
     * @ORM\ManyToOne(targetEntity="Country", inversedBy="playersCountries")
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
     * @var string
     *
     * @ORM\Column(name="is_competing", type="boolean")
     *
     * @Serializer\Groups({"players_overview"})
     */
    private $isCompeting = true;

    /**
     * @var string
     *
     * @ORM\Column(name="is_active", type="boolean")
     *
     * @Serializer\Groups({"players_overview"})
     */
    private $isActive = true;

    /**
     * @ORM\ManyToMany(targetEntity="Entrant", mappedBy="players")
     */
    private $entrants;

    /**
     *
     */
    public function __construct()
    {
        $this->entrants = new ArrayCollection();
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
    public function getGamerTag(): string
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
}

