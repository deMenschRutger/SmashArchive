<?php

declare(strict_types = 1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="entrant", indexes={
 *     @ORM\Index(name="smashgg_index", columns={"smashgg_id"}),
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
     * @ORM\Column(name="smashgg_id", type="integer", nullable=true)
     *
     * @TODO Rename to 'externalId'.
     */
    private $smashggId;

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
     * The event that resulted in the player becoming a part of the database.
     *
     * @var Event
     *
     * @ORM\ManyToOne(targetEntity="Event")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $originEvent;

    /**
     * Used for merging two entrants.
     *
     * @var Entrant
     *
     * @ORM\OneToOne(targetEntity="Entrant")
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
     * @return string
     */
    public function getExpandedName()
    {
        $event = $this->getOriginEvent();

        if ($event instanceof Event) {
            $event = $event->getName();
        } else {
            $event = 'Event unknown';
        }

        return sprintf('%s | %s | #%s', $this->name, $event, $this->getId());
    }

    /**
     * @return string
     */
    public function getNameWithPlayers()
    {
        $players = $this->getPlayers();

        if ($players->count() > 0) {
            $players = $players->map(function (Player $player) {
                return $player->getGamerTag();
            })->toArray();

            $joined = join(',', $players);

            if ($joined !== $this->name) {
                return sprintf('%s (%s)', $this->name, $joined);
            }
        }

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
     * @return Event
     */
    public function getOriginEvent()
    {
        return $this->originEvent;
    }

    /**
     * @return string
     */
    public function getOriginEventExpandedName()
    {
        if (!$this->originEvent instanceof Event) {
            return null;
        }

        return $this->originEvent->getExpandedName();
    }

    /**
     * @param Event $originEvent
     */
    public function setOriginEvent(Event $originEvent)
    {
        $this->originEvent = $originEvent;
    }

    /**
     * @return Entrant
     */
    public function getParentEntrant()
    {
        return $this->parentEntrant;
    }

    /**
     * @param Entrant $parentEntrant
     */
    public function setParentEntrant($parentEntrant)
    {
        $this->parentEntrant = $parentEntrant;
    }

    /**
     * @return Collection
     */
    public function getPlayers(): Collection
    {
        // This is a workaround for confusing behaviour in Doctrine where it loads certain associations multiple times.
        if (count($this->players) === 2 && $this->players[0] === $this->players[1]) {
            $this->players->remove(1);
        }

        return $this->players;
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
        $player->addEntrant($this);
        $this->players[] = $player;
    }

    /**
     * @param ArrayCollection $players
     */
    public function setPlayers(ArrayCollection $players)
    {
        $this->players = new ArrayCollection();

        foreach ($players as $player) {
            $this->addPlayer($player);
        }
    }

    /**
     * @return bool
     */
    public function isSinglePlayer()
    {
        return count($this->getPlayers()) === 1;
    }
}
