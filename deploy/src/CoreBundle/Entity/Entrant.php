<?php

declare(strict_types = 1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="entrant", indexes={
 *     @ORM\Index(name="external_id_index", columns={"external_id"}),
 *     @ORM\Index(name="name_index", columns={"name"}),
 *     @ORM\Index(name="created_at_index", columns={"created_at"}),
 *     @ORM\Index(name="updated_at_index", columns={"updated_at"}),
 * })
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\EntrantRepository")
 */
class Entrant
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $id;

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
     * @Serializer\Groups({"players_sets"})
     */
    private $name;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_new", type="boolean")
     */
    private $isNew = true;

    /**
     * The phase that - when imported - resulted in the player becoming a part of the database.
     *
     * @var Phase
     *
     * @ORM\ManyToOne(targetEntity="Phase")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $originPhase;

    /**
     * @var Entrant
     *
     * @ORM\OneToOne(targetEntity="Entrant")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $parentEntrant;

    /**
     * @ORM\OneToMany(targetEntity="Set", mappedBy="entrantOne")
     */
    private $entrantOneSets;

    /**
     * @ORM\OneToMany(targetEntity="Set", mappedBy="entrantTwo")
     */
    private $entrantTwoSets;

    /**
     * @ORM\ManyToMany(targetEntity="Player", inversedBy="entrants")
     * @ORM\JoinTable(name="entrants_players")
     */
    private $players;

    /**
     * @ORM\OneToMany(targetEntity="Result", mappedBy="entrant")
     */
    private $results;

    /**
     *
     */
    public function __construct()
    {
        $this->entrantOneSets = new ArrayCollection();
        $this->entrantTwoSets = new ArrayCollection();
        $this->players = new ArrayCollection();
        $this->results = new ArrayCollection();
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
        $id = $this->getId();
        $phase = $this->getOriginPhase();

        if (!$phase instanceof Phase) {
            return sprintf('%s (#%s) | unknown phase | unknown event | unknown tournament', $this->name, $id);
        }

        $event = $phase->getEvent();

        if (!$event instanceof Event) {
            return sprintf('%s (#%s) | %s | unknown event | unknown tournament', $this->name, $id, $phase->getName());
        }

        $tournament = $event->getTournament();

        if (!$tournament instanceof Tournament) {
            return sprintf('%s (#%s) | %s | %s | unknown tournament', $this->name, $id, $phase->getName(), $event->getName());
        }

        return sprintf('%s (#%s) | %s | %s | %s', $this->name, $id, $phase->getName(), $event->getName(), $tournament->getName());
    }

    /**
     * @return string
     */
    public function getNameWithPlayers()
    {
        $players = $this->getPlayers();

        if ($players->count() === 0) {
            return $this->getName();
        }

        $players = $players->map(function (Player $player) {
            $playerProfile = $player->getPlayerProfile();

            return $playerProfile instanceof PlayerProfile ? $playerProfile : null;
        })->toArray();

        $players = array_filter($players);

        if (count($players) === 0) {
            return $this->getName();
        }

        $joined = join(',', $players);

        if ($joined !== $this->getName()) {
            return sprintf('%s (%s)', $this->getName(), $joined);
        }

        return $this->getName();
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    public function setIsNew($isNew)
    {
        $this->isNew = $isNew;
    }

    /**
     * @return Phase|null
     */
    public function getOriginPhase()
    {
        return $this->originPhase;
    }

    /**
     * @return Event|null
     */
    public function getOriginEvent()
    {
        if ($this->originPhase instanceof Phase) {
            return $this->originPhase->getEvent();
        }

        return null;
    }

    /**
     * @return Tournament|null
     */
    public function getOriginTournament()
    {
        $originEvent = $this->getOriginEvent();

        if ($originEvent instanceof Event) {
            return $originEvent->getTournament();
        }

        return null;
    }

    /**
     * @param Phase $originPhase
     */
    public function setOriginPhase($originPhase)
    {
        $this->originPhase = $originPhase;
    }

    /**
     * @return Entrant
     */
    public function getParentEntrant()
    {
        return $this->parentEntrant;
    }
    /**
     * @return bool
     */
    public function hasParentEntrant()
    {
        return $this->parentEntrant instanceof Entrant;
    }

    /**
     * @param Entrant $parentEntrant
     */
    public function setParentEntrant($parentEntrant)
    {
        $this->parentEntrant = $parentEntrant;
    }

    /**
     * @return Set[]|ArrayCollection
     */
    public function getEntrantOneSets()
    {
        return $this->entrantOneSets;
    }

    /**
     * @return Set[]|ArrayCollection
     */
    public function getEntrantTwoSets()
    {
        return $this->entrantTwoSets;
    }

    /**
     * @return ArrayCollection
     */
    public function getPlayers(): ArrayCollection
    {
        // This is a workaround for confusing behaviour in Doctrine where it loads certain associations multiple times.
        $unique = new ArrayCollection();

        foreach ($this->players as $player) {
            if (!$unique->contains($player)) {
                $unique->add($player);
            }
        }

        return $unique;
    }

    /**
     * @param Player $player
     * @return bool
     */
    public function hasPlayer(Player $player)
    {
        return $this->players->contains($player);
    }

    /**
     * @param Player $player
     */
    public function addPlayer(Player $player)
    {
        if (!$this->players->contains($player)) {
            $player->addEntrant($this);
            $this->players->add($player);
        }
    }

    /**
     * @param Player $player
     */
    public function removePlayer(Player $player)
    {
        if (!$this->players->contains($player)) {
            $player->removeEntrant($this);
            $this->players->remove($player);
        }
    }

    /**
     * @param ArrayCollection $players
     */
    public function setPlayers(ArrayCollection $players)
    {
        $this->players = $this->players->filter(function (Player $player) use ($players) {
            return $players->contains($player);
        });

        foreach ($players as $player) {
            $this->addPlayer($player);
        }
    }

    /**
     * @return bool
     */
    public function hasPlayerProfiles()
    {
        foreach ($this->getPlayers() as $player) {
            if (!$player->hasPlayerProfile()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return bool
     */
    public function isSinglePlayer()
    {
        return count($this->getPlayers()) === 1;
    }

    /**
     * @return bool
     */
    public function isTeam()
    {
        return count($this->getPlayers()) > 1;
    }

    /**
     * Return the slug of the player profile if this is a single player entrant.
     *
     * @return string|null
     */
    public function getSlug()
    {
        if (!$this->isSinglePlayer()) {
            return null;
        }

        $playerProfile = $this->getPlayers()->first()->getPlayerProfile();

        if ($playerProfile instanceof PlayerProfile) {
            return $playerProfile->getSlug();
        }

        return null;
    }
}
