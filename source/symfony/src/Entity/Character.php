<?php

declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="characters", uniqueConstraints={
 *  @ORM\UniqueConstraint(name="name_game_unique", columns={"name", "game_id"})
 * })
 * @ORM\Entity(repositoryClass="App\Repository\CharacterRepository")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class Character
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"profiles_overview", "profiles_details"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=128)
     *
     * @Serializer\Expose
     * @Serializer\Groups({"profiles_overview", "profiles_details"})
     */
    private $name;

    /**
     * @var Game|null
     *
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="characters")
     * @ORM\JoinColumn(onDelete="SET NULL")
     *
     * @Serializer\Expose
     * @Serializer\Groups({"profiles_overview", "profiles_details"})
     */
    private $game;

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
     * @return Game
     */
    public function getGame(): ?Game
    {
        return $this->game;
    }

    /**
     * @param Game $game
     */
    public function setGame(?Game $game): void
    {
        $this->game = $game;
    }
}
