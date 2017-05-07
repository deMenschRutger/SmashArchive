<?php

declare(strict_types=1);

namespace Domain\Handler\Tournament;

use Domain\Command\Tournament\ResultsCommand;
use Domain\Handler\AbstractHandler;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class ResultsHandler extends AbstractHandler
{
    /**
     * @param ResultsCommand $command
     * @return array
     */
    public function handle(ResultsCommand $command)
    {
        $results = $this
            ->entityManager
            ->createQueryBuilder()
            ->select('r, en, p')
            ->from('CoreBundle:Result', 'r')
            ->join('r.entrant', 'en')
            ->join('en.players', 'p')
            ->join('r.event', 'e')
            ->where('e.id = :id')
            ->setParameter('id', $command->getEventId())
            ->orderBy('r.rank, en.name')
            ->getQuery()
            ->getResult()
        ;

        return $results;
    }
}
