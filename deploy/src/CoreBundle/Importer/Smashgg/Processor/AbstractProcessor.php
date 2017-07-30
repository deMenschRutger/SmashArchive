<?php

declare(strict_types = 1);

namespace CoreBundle\Importer\Smashgg\Processor;

use Doctrine\ORM\EntityManager;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractProcessor
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
}
