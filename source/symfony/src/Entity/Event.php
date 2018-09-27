<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="event", indexes={
 *     @ORM\Index(name="external_id_index", columns={"external_id"}),
 *     @ORM\Index(name="name_index", columns={"name"}),
 *     @ORM\Index(name="created_at_index", columns={"created_at"}),
 *     @ORM\Index(name="updated_at_index", columns={"updated_at"}),
 * })
 * @ORM\Entity(repositoryClass="App\Repository\EventRepository")
 *
 * @Serializer\ExclusionPolicy("all")
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
     * @Serializer\Expose
     * @Serializer\Groups({"profiles_sets", "tournaments_details"})
     */
    private $id;

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
     * @Serializer\Groups({"profiles_ranks", "profiles_sets", "tournaments_details"})
     */
    private $name;

    /**
     * @var int|null
     *
     * @ORM\Column(name="entrant_count", type="integer", nullable=true)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"tournaments_overview", "tournaments_details"})
     */
    private $entrantCount;

    /**
     * @var Tournament|null
     *
     * @ORM\ManyToOne(targetEntity="Tournament", inversedBy="events")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"profiles_ranks", "profiles_sets"})
     */
    private $tournament;

    /**
     * @var Game|null
     *
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="events")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"profiles_ranks", "profiles_sets", "tournaments_details"})
     */
    private $game;

    /**
     * @var Phase[]
     *
     * @ORM\OneToMany(targetEntity="Phase", mappedBy="event")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"tournaments_details"})
     */
    private $phases;

    /**
     * @var Rank[]
     *
     * @ORM\OneToMany(targetEntity="Rank", mappedBy="event")
     */
    private $ranks;

    /**
     *
     */
    public function __construct()
    {
        $this->phases = new ArrayCollection();
        $this->ranks = new ArrayCollection();
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
    public function getExternalId(): string
    {
        return $this->externalId;
    }

    /**
     * @param string $externalId
     */
    public function setExternalId(string $externalId): void
    {
        $this->externalId = $externalId;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getExpandedName(): string
    {
        return sprintf('%s (%s)', $this->name, $this->getTournament()->getName());
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getEntrantCount(): int
    {
        return $this->entrantCount;
    }

    /**
     * @param int $entrantCount
     */
    public function setEntrantCount(int $entrantCount): void
    {
        $this->entrantCount = $entrantCount;
    }

    /**
     * @return Tournament
     */
    public function getTournament(): Tournament
    {
        return $this->tournament;
    }

    /**
     * @param Tournament $tournament
     */
    public function setTournament(Tournament $tournament): void
    {
        $this->tournament = $tournament;
    }

    /**
     * @return Game|null
     */
    public function getGame(): ?Game
    {
        return $this->game;
    }

    /**
     * @param Game|null $game
     */
    public function setGame(?Game $game): void
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
     * @return Rank[]|Collection
     */
    public function getRanks(): Collection
    {
        return $this->ranks;
    }

    /**
     * @return Player[]|ArrayCollection
     */
    public function getPlayers(): array
    {
        $players = [];

        /** @var Phase $phase */
        foreach ($this->getPhases() as $phase) {
            $players = array_merge($players, $phase->getPlayers());
        }

        return array_unique($players);
    }
}
