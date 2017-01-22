<?php

declare(strict_types=1);

namespace CoreBundle\Entity;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="phase")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\PhaseRepository")
 */
class Phase
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
     * @var int
     *
     * @ORM\Column(name="phaseOrder", type="integer")
     */
    private $phaseOrder;

    /**
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="phases")
     */
    private $event;

    /**
     * @ORM\OneToMany(targetEntity="PhaseGroup", mappedBy="phase")
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

