<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="game", indexes={
 *     @ORM\Index(name="smashgg_index", columns={"smashgg_id"}),
 *     @ORM\Index(name="name_index", columns={"name"}),
 *     @ORM\Index(name="created_at_index", columns={"created_at"}),
 *     @ORM\Index(name="updated_at_index", columns={"updated_at"}),
 * })
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\GameRepository")
 */
class Game
{
    use TimestampableTrait;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Groups({"players_sets", "tournaments_details", "players_overview"})
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="smashgg_id", type="integer", nullable=true)
     */
    private $smashggId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128, unique=true)
     *
     * @Serializer\Groups({"players_sets", "tournaments_details", "players_overview"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="display_name", type="string", length=255)
     *
     * @Serializer\Groups({"players_sets", "tournaments_details", "players_overview"})
     */
    private $displayName;

    /**
     * @var Character[]
     *
     * @ORM\OneToMany(targetEntity="Character", mappedBy="game")
     */
    private $characters;

    /**
     * @var Event[]
     *
     * @ORM\OneToMany(targetEntity="Event", mappedBy="game")
     */
    private $events;

    /**
     *
     */
    public function __construct()
    {
        $this->events = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getSmashggId(): int
    {
        return $this->smashggId;
    }

    /**
     * @param int $smashggId
     */
    public function setSmashggId(int $smashggId): void
    {
        $this->smashggId = $smashggId;
    }

    /**
     * @return string
     */
    public function getName(): string
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
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName(string $displayName): void
    {
        $this->displayName = $displayName;
    }

    /**
     * @return Character[]|ArrayCollection
     */
    public function getCharacters(): array
    {
        return $this->characters;
    }

    /**
     * @return Event[]|ArrayCollection
     */
    public function getEvents(): array
    {
        return $this->events;
    }
}
