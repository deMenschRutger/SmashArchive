<?php

declare(strict_types=1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="entrant")
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
     */
    private $name;

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
     *
     */
    public function __construct()
    {
        $this->entrantOneSets = new ArrayCollection();
        $this->entrantTwoSets = new ArrayCollection();
        $this->players = new ArrayCollection();
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
    public function getName(): string
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
     * @return Collection
     */
    public function getPlayers(): Collection
    {
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
}

