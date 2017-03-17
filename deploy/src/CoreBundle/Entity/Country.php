<?php

declare(strict_types=1);

namespace CoreBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="country")
 * @ORM\Entity(repositoryClass="CoreBundle\Repository\CountryRepository")
 */
class Country
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
     * @ORM\Column(name="code", type="string", length=255, unique=true)
     */
    private $code;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255)
     */
    private $name;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Player", mappedBy="nationality")
     */
    private $playersNationalities;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Player", mappedBy="country")
     */
    private $playersCountries;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Tournament", mappedBy="country")
     */
    private $tournaments;

    /**
     *
     */
    public function __construct()
    {
        $this->playersNationalities = new ArrayCollection();
        $this->playersCountries = new ArrayCollection();
        $this->tournaments = new ArrayCollection();
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->name ? $this->getName() : '';
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
    public function getCode(): string
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode(string $code)
    {
        $this->code = $code;
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
     * @return ArrayCollection
     */
    public function getPlayersNationalities(): ArrayCollection
    {
        return $this->playersNationalities;
    }

    /**
     * @return ArrayCollection
     */
    public function getPlayersCountries(): ArrayCollection
    {
        return $this->playersCountries;
    }

    /**
     * @return ArrayCollection
     */
    public function getTournaments(): ArrayCollection
    {
        return $this->tournaments;
    }
}
