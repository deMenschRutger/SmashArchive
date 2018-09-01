<?php

declare(strict_types = 1);

namespace App\Importer;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractProcessor
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
