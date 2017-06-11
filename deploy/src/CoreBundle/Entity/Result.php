<?php

declare(strict_types = 1);

namespace CoreBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="result", indexes={
 *     @ORM\Index(name="rank_index", columns={"rank"}),
 * })
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\ResultRepository")
 */
class Result
{
    /**
     * @var Event
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="results")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $event;

    /**
     * @var Entrant
     *
     * @ORM\Id
     * @ORM\ManyToOne(targetEntity="Entrant", inversedBy="results")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    private $entrant;

    /**
     * @var integer
     *
     * @ORM\Column(name="rank", type="integer")
     *
     * @Serializer\Groups({"tournaments_results"})
     */
    private $rank;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getStringifiedRank();
    }

    /**
     * @return Event
     */
    public function getEvent(): Event
    {
        return $this->event;
    }

    /**
     * @param Event $event
     */
    public function setEvent(Event $event)
    {
        $this->event = $event;
    }

    /**
     * @return Entrant
     */
    public function getEntrant(): Entrant
    {
        return $this->entrant;
    }

    /**
     * @return string
     *
     * @Serializer\Groups({"tournaments_results"})
     * @Serializer\SerializedName("entrant")
     * @Serializer\VirtualProperty()
     */
    public function getEntrantName(): string
    {
        return $this->getEntrant()->getName();
    }

    /**
     * @return Collection
     *
     * @Serializer\Groups({"tournaments_results"})
     * @Serializer\VirtualProperty()
     */
    public function getPlayers()
    {
        return $this->getEntrant()->getPlayers();
    }

    /**
     * @param Entrant $entrant
     */
    public function setEntrant(Entrant $entrant)
    {
        $this->entrant = $entrant;
    }

    /**
     * @return int
     */
    public function getRank(): int
    {
        return $this->rank;
    }

    /**
     * @return string
     */
    public function getStringifiedRank(): string
    {
        $formatter = new \NumberFormatter('en_US', \NumberFormatter::ORDINAL);

        return $formatter->format($this->rank);
    }

    /**
     * @param int $rank
     */
    public function setRank(int $rank)
    {
        $this->rank = $rank;
    }
}
