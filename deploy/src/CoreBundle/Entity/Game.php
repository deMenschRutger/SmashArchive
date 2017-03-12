<?php

declare(strict_types=1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="game")
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
     * @Serializer\Groups({"players_results", "tournaments_details"})
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
     * @ORM\Column(name="name", type="string", length=255, unique=true)
     *
     * @Serializer\Groups({"players_results", "tournaments_details"})
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="display_name", type="string", length=255)
     *
     * @Serializer\Groups({"players_results", "tournaments_details"})
     */
    private $displayName;

    /**
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
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getSmashggId()
    {
        return $this->smashggId;
    }

    /**
     * @param int $smashggId
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
     * @return string
     */
    public function getDisplayName(): string
    {
        return $this->displayName;
    }

    /**
     * @param string $displayName
     */
    public function setDisplayName(string $displayName)
    {
        $this->displayName = $displayName;
    }

    /**
     * @return Collection
     */
    public function getEvents(): Collection
    {
        return $this->events;
    }
}
