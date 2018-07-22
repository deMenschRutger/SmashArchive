<?php

declare(strict_types = 1);

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(
 *     name="users",
 *     uniqueConstraints={
 *         @ORM\UniqueConstraint(name="provider_provider_id", columns={"provider", "provider_id"}),
 *     }
 * )
 */
class User implements UserInterface
{
    /**
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255)
     */
    protected $username;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=32)
     */
    protected $provider;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=128)
     */
    protected $providerId;

    /**
     * @var array
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
