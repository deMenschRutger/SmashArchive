<?php

declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Swagger\Annotations as SWG;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="users")
 *
 * @Serializer\ExclusionPolicy("all")
 */
class User implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     * @Serializer\Expose()
     *
     * @SWG\Property(example=1)
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="encrypted")
     *
     * @Serializer\Expose()
     * @Serializer\Type("string")
     *
     * @SWG\Property(example="JohnDoe")
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $provider;

    /**
     * @var string
     *
     * @ORM\Column(type="encrypted")
     */
    protected $providerId;

    /**
     * @var string
     *
     * @ORM\Column(type="hashed", length=64, unique=true)
     */
    protected $providerHash;

    /**
     * @var array
     *
     * @ORM\Column(type="json_array")
     */
    protected $roles = [];

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * This method exists to accommodate the JWT builder.
     *
     * @return int
     */
    public function getSub(): int
    {
        return $this->getId();
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
    }

    /**
     * @return string
     */
    public function getProvider(): string
    {
        return $this->provider;
    }

    /**
     * @param string $provider
     */
    public function setProvider(string $provider): void
    {
        $this->provider = $provider;
    }

    /**
     * @return string
     */
    public function getProviderId(): string
    {
        return $this->providerId;
    }

    /**
     * @param string $providerId
     */
    public function setProviderId(string $providerId): void
    {
        $this->providerId = $providerId;
    }

    /**
     * @return string
     */
    public function getProviderHash(): string
    {
        return $this->providerHash;
    }

    /**
     * @return void
     */
    public function setProviderHash(): void
    {
        $this->providerHash = $this->getProvider().'_'.$this->getProviderId();
    }

    /**
     * @return void
     */
    public function getPassword(): void
    {
        return;
    }

    /**
     * @return void
     */
    public function getSalt(): void
    {
        return;
    }

    /**
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param array $roles
     */
    public function setRoles(array $roles): void
    {
        $this->roles = $roles;
    }

    /**
     * @return void
     */
    public function eraseCredentials(): void
    {
        return;
    }
}
