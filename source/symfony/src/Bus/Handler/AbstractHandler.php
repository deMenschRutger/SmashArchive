<?php

declare(strict_types = 1);

namespace App\Bus\Handler;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var SymfonyStyle|null
     */
    protected $io;

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager): void
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $name
     *
     * @return ObjectRepository
     */
    public function getRepository(string $name): ObjectRepository
    {
        return $this->entityManager->getRepository($name);
    }

    /**
     * @return SymfonyStyle|null
     */
    public function getIo(): ?SymfonyStyle
    {
        return $this->io;
    }

    /**
     * @param SymfonyStyle|null $io
     */
    public function setIo(?SymfonyStyle $io): void
    {
        $this->io = $io;
    }
}
