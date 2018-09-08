<?php

declare(strict_types = 1);

namespace App\Importer;

use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use League\Tactician\CommandBus;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractImporter
{
    /**
     * @var SymfonyStyle
     */
    protected $io;

    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @var CommandBus
     */
    protected $commandBus;

    /**
     * @return SymfonyStyle
     */
    public function getIo()
    {
        return $this->io;
    }

    /**
     * @param SymfonyStyle $io
     */
    public function setIo(SymfonyStyle $io)
    {
        $this->io = $io;
    }

    /**
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function setEntityManager(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $name
     *
     * @return ObjectRepository
     */
    public function getRepository(string $name)
    {
        return $this->entityManager->getRepository($name);
    }

    /**
     * @return CommandBus
     */
    public function getCommandBus()
    {
        return $this->commandBus;
    }

    /**
     * @param CommandBus $commandBus
     */
    public function setCommandBus($commandBus)
    {
        $this->commandBus = $commandBus;
    }
}
