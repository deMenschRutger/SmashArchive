<?php

declare(strict_types = 1);

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="characters", uniqueConstraints={
 *  @ORM\UniqueConstraint(name="name_game_unique", columns={"name", "game_id"})
 * })
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\CharacterRepository")
 */
class Character
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity="Game", inversedBy="characters")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $game;

    /**
     * @return string
     */
    public function __toString()
    {
        $game = $this->getGame();

        if (!$game instanceof Game) {
            return $this->getName();
        }

        return sprintf('%s (%s)', $this->getName(), $game->getDisplayName());
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
}
