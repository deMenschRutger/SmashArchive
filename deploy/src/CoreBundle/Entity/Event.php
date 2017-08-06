<?php

declare(strict_types = 1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="event", indexes={
 *     @ORM\Index(name="smashgg_index", columns={"smashgg_id"}),
 *     @ORM\Index(name="name_index", columns={"name"}),
 *     @ORM\Index(name="created_at_index", columns={"created_at"}),
 *     @ORM\Index(name="updated_at_index", columns={"updated_at"}),
 * })
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\EventRepository")
 */
class Event
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"players_sets", "tournaments_details"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="smashgg_id", type="integer", nullable=true)
     */
    private $smashggId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Serializer\Groups({"players_sets", "tournaments_details"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     *
     * @Serializer\Groups({"tournaments_details"})
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="Tournament", inversedBy="events")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $tournament;

    /**
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="events")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Serializer\Groups({"players_sets", "tournaments_details"})
     */
    private $game;

    /**
     * @ORM\OneToMany(targetEntity="Phase", mappedBy="event")
     *
     * @Serializer\Groups({"tournaments_details"})
     */
    private $phases;

    /**
     * @ORM\OneToMany(targetEntity="Result", mappedBy="event")
     */
    private $results;

    /**
     *
     */
    public function __construct()
    {
        $this->phases = new ArrayCollection();
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
    public function getId(): int
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
     * @param string $name
     */
    public function setName(string $name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return Tournament
     */
    public function getTournament()
    {
        return $this->tournament;
    }

    /**
     * @param Tournament $tournament
     */
    public function setTournament(Tournament $tournament)
    {
        $this->tournament = $tournament;
    }

    /**
     * @return Game
     */
    public function getGame()
    {
        return $this->game;
    }

    /**
     * @param Game $game
     */
    public function setGame(Game $game)
    {
        $this->game = $game;
    }

    /**
     * @return Collection
     */
    public function getPhases(): Collection
    {
        return $this->phases;
    }

    /**
     * @return ArrayCollection
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * @return Player[]
     */
    public function getPlayers()
    {
        $players = [];

        /** @var Phase $phase */
        foreach ($this->getPhases() as $phase) {
            $players = array_merge($players, $phase->getPlayers());
        }

        return array_unique($players);
    }
}
