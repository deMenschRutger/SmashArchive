<?php

declare(strict_types = 1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="player", indexes={
 *     @ORM\Index(name="name_index", columns={"name"}),
 *     @ORM\Index(name="type_index", columns={"type"}),
 *     @ORM\Index(name="external_id_index", columns={"external_id"}),
 *     @ORM\Index(name="created_at_index", columns={"created_at"}),
 *     @ORM\Index(name="updated_at_index", columns={"updated_at"}),
 * })
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\PlayerRepository")
 */
class Player
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
     * @Serializer\Groups({"players_overview"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string")
     */
    private $type = self::SOURCE_CUSTOM;

    /**
     * @var string
     *
     * @ORM\Column(name="external_id", type="string", nullable=true)
     */
    private $externalId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Serializer\Groups({"players_overview", "tournaments_details"})
     */
    private $name;

    /**
     * @ORM\ManyToMany(targetEntity="Entrant", mappedBy="players")
     */
    private $entrants;

    /**
     * The tournament that resulted in the player becoming a part of the database.
     *
     * @ORM\ManyToOne(targetEntity="Tournament")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $originTournament;

    /**
     * @ORM\ManyToOne(targetEntity="PlayerProfile", inversedBy="players")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $playerProfile;

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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return string
     */
    public function getExpandedName()
    {
        $name = sprintf('%s - %s', $this->getName(), $this->getType());

        if ($this->getExternalId()) {
            $name .= sprintf(' (#%s)', $this->getExternalId());
        }

        return $name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @param Entrant $entrant
     */
    public function removeEntrant(Entrant $entrant)
    {
        if ($this->entrants->contains($entrant)) {
            $this->entrants->remove($entrant);
        }
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
     * @return PlayerProfile
     */
    public function getPlayerProfile()
    {
        return $this->playerProfile;
    }

    /**
     * @return bool
     */
    public function hasPlayerProfile()
    {
        return $this->playerProfile instanceof PlayerProfile;
    }

    /**
     * @param PlayerProfile $playerProfile
     */
    public function setPlayerProfile($playerProfile)
    {
        $this->playerProfile = $playerProfile;
    }

    /**
     * @return string|null
     */
    public function getSlug()
    {
        $playerProfile = $this->getPlayerProfile();

        if ($playerProfile instanceof PlayerProfile) {
            return $playerProfile->getSlug();
        }

        return null;
    }

    /**
     * @return string|null
     */
    public function getGamerTag()
    {
        $playerProfile = $this->getPlayerProfile();

        if ($playerProfile instanceof PlayerProfile) {
            return $playerProfile->getGamerTag();
        }

        return null;
    }
}
