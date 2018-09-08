<?php

declare(strict_types = 1);

namespace CoreBundle\Importer;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
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
     * @var EntityManager
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
     * @return EntityManager
     */
    public function getEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $name
     * @return EntityRepository
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
