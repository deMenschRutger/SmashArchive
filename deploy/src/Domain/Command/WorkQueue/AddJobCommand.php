<?php

declare(strict_types = 1);

namespace Domain\Command\WorkQueue;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class AddJobCommand
{
    /**
     * @var string
     */
    private $tube;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $job;

    /**
     * @param string $tube
     * @param string $name
     * @param array  $job
     */
    public function __construct(string $tube, string $name, array $job)
    {
        $this->tube = $tube;
        $this->name = $name;
        $this->job = $job;
    }

    /**
     * @return string
     */
    public function getTube(): string
    {
        return $this->tube;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getJob(): array
    {
        return $this->job;
    }
}
