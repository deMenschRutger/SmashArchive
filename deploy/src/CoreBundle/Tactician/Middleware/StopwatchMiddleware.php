<?php

declare(strict_types = 1);

namespace CoreBundle\Tactician\Middleware;

use League\Tactician\Middleware;
use Symfony\Component\Stopwatch\Stopwatch;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class StopwatchMiddleware implements Middleware
{
    /**
     * @var Stopwatch
     */
    protected $stopwatch;

    /**
     * @param Stopwatch $stopwatch
     */
    public function __construct($stopwatch)
    {
        $this->stopwatch = $stopwatch;
    }

    /**
     * @param object   $command
     * @param callable $next
     * @return mixed
     *
     * @TODO Only use the stopwatch if debug mode is enabled.
     */
    public function execute($command, callable $next)
    {
        $commandClass = get_class($command);

        if ($this->stopwatch instanceof Stopwatch) {
            $this->stopwatch->start($commandClass);
            $returnValue = $next($command);
            $this->stopwatch->stop($commandClass);
        } else {
            $returnValue = $next($command);
        }

        return $returnValue;
    }
}
