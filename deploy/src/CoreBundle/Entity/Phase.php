<?php

declare(strict_types=1);

namespace CoreBundle\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="phase")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\PhaseRepository")
 */
class Phase
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
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Serializer\Groups({"players_results", "tournaments_details"})
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="phaseOrder", type="integer")
     *
     * @Serializer\Groups({"tournaments_details"})
     */
    private $phaseOrder;

    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="phases")
     *
     * @Serializer\Groups({"players_results"})
     */
    private $event;

    /**
     * @ORM\OneToMany(targetEntity="PhaseGroup", mappedBy="phase")
     *
     * @Serializer\Groups({"tournaments_details"})
     */
    private $phaseGroups;

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
     * @return int
     */
    public function getPhaseOrder(): int
    {
        return $this->phaseOrder;
    }

    /**
     * @param integer $phaseOrder
     */
    public function setPhaseOrder(int $phaseOrder)
    {
        $this->phaseOrder = $phaseOrder;
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
     * @return Collection
     */
    public function getPhaseGroups(): Collection
    {
        return $this->phaseGroups;
    }
}

