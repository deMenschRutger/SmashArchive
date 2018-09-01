<?php

declare(strict_types = 1);

namespace App\Entity;

use App\Entity\Traits\TimestampableTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * @ORM\Table(name="player", indexes={
 *     @ORM\Index(name="name_index", columns={"name"}),
 *     @ORM\Index(name="type_index", columns={"type"}),
 *     @ORM\Index(name="external_id_index", columns={"external_id"}),
 *     @ORM\Index(name="created_at_index", columns={"created_at"}),
 *     @ORM\Index(name="updated_at_index", columns={"updated_at"}),
 * })
 * @ORM\Entity(repositoryClass="App\Repository\PlayerRepository")
 */
class Player
{
    const SOURCE_CUSTOM       = 'custom';
    const SOURCE_SMASHGG      = 'smashgg';
    const SOURCE_CHALLONGE    = 'challonge';
    const SOURCE_TIO          = 'tio';
    const SOURCE_SMASHRANKING = 'smashranking';

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
     * @ORM\Column(name="type", type="string")
     */
    private $type = self::SOURCE_CUSTOM;

    /**
     * @var string|null
     *
     * @ORM\Column(name="external_id", type="string", nullable=true)
     */
    private $externalId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name;

    /**
     * @var Player[]
     *
     * @ORM\ManyToMany(targetEntity="Entrant", mappedBy="players")
     */
    private $entrants;

    /**
     * @var Tournament|null
     *
     * The tournament that resulted in the player becoming a part of the database.
     *
     * @ORM\ManyToOne(targetEntity="Tournament")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $originTournament;

    /**
     * @var Profile|null
     *
     * @ORM\ManyToOne(targetEntity="Profile", inversedBy="players")
     * @ORM\JoinColumn(onDelete="SET NULL")
     */
    private $profile;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return 'Player #'.$this->getId();
    }

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
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType(string $type): void
    {
        $this->type = $type;
    }

    /**
     * @return string|null
     */
    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    /**
     * @param string|null $externalId
     */
    public function setExternalId(?string $externalId): void
    {
        $this->externalId = $externalId;
    }

    /**
     * @return string|null
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getExpandedName(): string
    {
        $name = sprintf('%s - %s', $this->getName(), $this->getType());

        if ($this->getExternalId()) {
            $name .= sprintf(' (#%s)', $this->getExternalId());
        }

        return $name;
    }

    /**
     * @param string|null $name
     */
    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Entrant[]|Collection
     */
    public function getEntrants(): Collection
    {
        return $this->entrants;
    }

    /**
     * @param Entrant $entrant
     */
    public function addEntrant(Entrant $entrant): void
    {
        $this->entrants[] = $entrant;
    }

    /**
     * @param Entrant $entrant
     */
    public function removeEntrant(Entrant $entrant): void
    {
        if ($this->entrants->contains($entrant)) {
            $this->entrants->remove($entrant);
        }
    }

    /**
     * @return Tournament|null
     */
    public function getOriginTournament(): ?Tournament
    {
        return $this->originTournament;
    }

    /**
     * @param Tournament|null $originTournament
     */
    public function setOriginTournament(?Tournament $originTournament): void
    {
        $this->originTournament = $originTournament;
    }

    /**
     * @return Profile|null
     */
    public function getProfile(): ?Profile
    {
        return $this->profile;
    }

    /**
     * @return bool
     */
    public function hasProfile(): bool
    {
        return $this->profile instanceof Profile;
    }

    /**
     * @param Profile|null $profile
     */
    public function setProfile(?Profile $profile): void
    {
        $this->profile = $profile;
    }

    /**
     * @return string|null
     *
     * @Serializer\Groups({"tournaments_results", "players_sets"})
     * @Serializer\VirtualProperty()
     */
    public function getSlug(): ?string
    {
        $profile = $this->getProfile();

        if ($profile instanceof Profile) {
            return $profile->getSlug();
        }

        return null;
    }

    /**
     * @return string|null
     *
     * @Serializer\Groups({"tournaments_results", "players_sets"})
     * @Serializer\VirtualProperty()
     */
    public function getGamerTag()
    {
        $profile = $this->getProfile();

        if ($profile instanceof Profile) {
            return $profile->getGamerTag();
        }

        return null;
    }
}
