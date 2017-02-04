<?php

declare(strict_types=1);

namespace Domain\Handler;

use Doctrine\ORM\EntityManager;

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
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
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
}
