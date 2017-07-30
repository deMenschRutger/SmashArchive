<?php

declare(strict_types = 1);

namespace Domain\Command\WorkQueue;

use Pheanstalk\Job;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ProcessJobCommand
{
    /**
     * @var Job
     */
    private $job;

    /**
     * @var SymfonyStyle
     */
    private $io;

    /**
     * @param Job          $job
     * @param SymfonyStyle $io
     */
    public function __construct(Job $job, $io)
    {
        $this->job = $job;
        $this->io = $io;
    }

    /**
     * @return Job
     */
    public function getJob(): Job
    {
        return $this->job;
    }

    /**
     * @return SymfonyStyle
     */
    public function getIo()
    {
        return $this->io;
    }
}
