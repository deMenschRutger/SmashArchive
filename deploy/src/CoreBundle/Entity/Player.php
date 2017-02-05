<?php

declare(strict_types=1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Mapping\Annotation as Gedmo;

/**
 * @ORM\Table(name="player")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\PlayerRepository")
 */
class Player
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
     * @Gedmo\Slug(fields={"gamerTag"})
     * @ORM\Column(name="slug", type="string", length=255, unique=true)
     */
    private $slug;

    /**
     * @var string
     *
     * @ORM\Column(name="smashgg_id", type="integer", nullable=true)
     */
    private $smashggId;

    /**
     * @var string
     *
     * @ORM\Column(name="gamer_tag", type="string")
     */
    private $gamerTag;

    /**
     * @ORM\ManyToMany(targetEntity="Entrant", mappedBy="players")
     */
    private $entrants;

    /**
     *
     */
    public function __construct()
    {
        $this->entrants = new ArrayCollection();
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
    public function getSlug()
    {
        return $this->slug;
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
    public function getGamerTag(): string
    {
        return $this->gamerTag;
    }

    /**
     * @param string $gamerTag
     */
    public function setGamerTag(string $gamerTag)
    {
        $this->gamerTag = $gamerTag;
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
}

