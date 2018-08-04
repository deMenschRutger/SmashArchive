<?php

declare(strict_types = 1);

namespace App\Entity;

use CoreBundle\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="phase", indexes={
 *     @ORM\Index(name="external_id_index", columns={"external_id"}),
 *     @ORM\Index(name="name_index", columns={"name"}),
 *     @ORM\Index(name="phase_order_index", columns={"phase_order"}),
 *     @ORM\Index(name="created_at_index", columns={"created_at"}),
 *     @ORM\Index(name="updated_at_index", columns={"updated_at"}),
 * })
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
     * @Serializer\Groups({"players_sets", "tournaments_details"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="external_id", type="string", length=255, nullable=true)
     */
    private $externalId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     *
     * @Serializer\Groups({"players_sets", "tournaments_details"})
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="phase_order", type="integer")
     *
     * @Serializer\Groups({"tournaments_details"})
     */
    private $phaseOrder;

    /**
     * @var Event|null
     *
     * @ORM\ManyToOne(targetEntity="Event", inversedBy="phases")
     * @ORM\JoinColumn(onDelete="CASCADE")
     *
     * @Serializer\Groups({"players_sets"})
     */
    private $event;

    /**
     * @var PhaseGroup[]|ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="PhaseGroup", mappedBy="phase", cascade={"persist"}, orphanRemoval=true)
     *
     * @Serializer\Groups({"tournaments_details"})
     */
    private $phaseGroups;

    /**
     *
     */
    public function __construct()
    {
        $this->phaseGroups = new ArrayCollection();
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
     * @param string $name
     */
    public function setName(string $name): void
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
    public function setPhaseOrder(int $phaseOrder): void
    {
        $this->phaseOrder = $phaseOrder;
    }

    /**
     * @return Event|null
     */
    public function getEvent(): ?Event
    {
        return $this->event;
    }

    /**
     * @param Event|null $event
     */
    public function setEvent(?Event $event): void
    {
        $this->event = $event;
    }

    /**
     * @return PhaseGroup[]|ArrayCollection
     */
    public function getPhaseGroups(): array
    {
        return $this->phaseGroups;
    }

    /**
     * @param PhaseGroup $phaseGroup
     */
    public function addPhaseGroup(PhaseGroup $phaseGroup): void
    {
        $this->phaseGroups->add($phaseGroup);
        $phaseGroup->setPhase($this);
    }

    /**
     * @param PhaseGroup[]|Collection $phaseGroups
     */
    public function setPhaseGroups(array $phaseGroups): void
    {
        foreach ($phaseGroups as $phaseGroup) {
            $this->addPhaseGroup($phaseGroup);
        }
    }

    /**
     * @return Player[]
     */
    public function getPlayers(): array
    {
        $players = [];

        /** @var PhaseGroup $phaseGroup */
        foreach ($this->getPhaseGroups() as $key => $phaseGroup) {
            $players = array_merge($players, $phaseGroup->getPlayers());
        }

        return array_unique($players);
    }
}
