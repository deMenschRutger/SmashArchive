<?php

declare(strict_types = 1);

namespace Domain\Handler\Event;

use CoreBundle\Bracket\SingleElimination\Bracket as SingleEliminationBracket;
use CoreBundle\Bracket\DoubleElimination\Bracket as DoubleEliminationBracket;
use CoreBundle\Bracket\RoundRobin\Bracket as RoundRobinBracket;
use CoreBundle\Entity\Event;
use CoreBundle\Entity\Phase;
use CoreBundle\Entity\PhaseGroup;
use CoreBundle\Entity\Result;
use CoreBundle\Repository\EventRepository;
use Domain\Command\Event\GenerateResultsCommand;
use Domain\Handler\AbstractHandler;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @TODO Make sure state is handled correctly when the handler is used multiple times.
 */
class GenerateResultsHandler extends AbstractHandler
{
    /**
     * @var Result[]
     */
    protected $combinedResults = [];

    /**
     * @param GenerateResultsCommand $command
     */
    public function handle(GenerateResultsCommand $command)
    {
        $this->entityManager->getConfiguration()->setSQLLogger(null);
        $eventId = $command->getEventId();

        $this->setIo($command->getIo());
        $this->io->writeln(sprintf('Generating results for event #%s...', $eventId));

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getRepository('CoreBundle:Event');
        /** @var Event $event */
        $event = $eventRepository->find($eventId);

        if (!$event instanceof Event) {
            throw new \InvalidArgumentException('The event could not be found.');
        }

        $eventRepository->deleteResults($event);
        $phases = $eventRepository->getOrderedPhases($command->getEventId());

        if (count($phases) === 0) {
            $this->io->writeln('Could not find any phases for the event, skipping...');

            return;
        }

        $this->combinedResults = $this->getPhaseResults($event, array_shift($phases));
        $this->combinedResults = $this->normalizeResults($this->combinedResults);

        /** @var Phase $phase */
        foreach ($phases as $phase) {
            $phaseResults = $this->getPhaseResults($event, $phase);
            $phaseHighestRank = $this->getHighestRank($phaseResults);

            $this->processResults($phaseResults, $phaseHighestRank);
        }

        foreach ($this->combinedResults as $result) {
            $this->entityManager->persist($result);
        }

        $this->io->writeln('Flushing entity manager...');

        $this->entityManager->flush();
    }

    /**
     * @param Event $event
     * @param Phase $phase
     * @return array
     */
    protected function getPhaseResults(Event $event, Phase $phase)
    {
        $results = [];

        /** @var PhaseGroup $phaseGroup */
        foreach ($phase->getPhaseGroups() as $phaseGroup) {
            $bracket = null;

            switch ($phaseGroup->getType()) {
                case PhaseGroup::TYPE_SINGLE_ELIMINATION:
                    $bracket = new SingleEliminationBracket($phaseGroup);
                    break;

                case PhaseGroup::TYPE_DOUBLE_ELIMINATION:
                    $bracket = new DoubleEliminationBracket($phaseGroup);
                    break;

                case PhaseGroup::TYPE_ROUND_ROBIN:
                    $bracket = new RoundRobinBracket($phaseGroup);
                    break;

                default:
                    continue 2;
            }

            $results = array_merge($results, $bracket->getResults($event));
        }

        // Occasionally it might happen that data was incorrectly entered and a player exists in a phase multiple times. Here we filter
        // out the duplicate results for those players.
        $existingEntrants = [];

        /** @var Result $result */
        foreach ($results as $key => $result) {
            $entrantId = $result->getEntrant()->getId();

            if (in_array($entrantId, $existingEntrants)) {
                unset($results[$key]);
                continue;
            }

            $existingEntrants[] = $entrantId;
        }

        return $results;
    }

    /**
     * @param Result[] $results
     * @return Result[]
     */
    protected function normalizeResults(array $results)
    {
        $resultsPerRank = [];

        foreach ($results as $result) {
            $rank = $result->getRank();

            if (!array_key_exists($rank, $resultsPerRank)) {
                $resultsPerRank[$rank] = [];
            }

            $resultsPerRank[$rank][] = $result;
        }

        ksort($resultsPerRank);

        $normalizedResults = [];
        $currentRank = 1;

        foreach ($resultsPerRank as $rank => $rankResults) {
            /** @var Result $result */
            foreach ($rankResults as $result) {
                $result->setRank($currentRank);
                $normalizedResults[] = $result;
            }

            $currentRank += count($rankResults);
        }

        return $normalizedResults;
    }

    /**
     * @param Result[] $results
     * @param int      $highestRank
     */
    protected function processResults(array $results, $highestRank)
    {
        $results = $this->normalizeResults($results);

        // Filter out all entrants that are in the new results group from the combined results group.
        $ranksByEntrantId = $this->getRanksByEntrantId($results);

        $this->combinedResults = array_filter($this->combinedResults, function (Result $result) use ($ranksByEntrantId) {
            $entrantId = $result->getEntrant()->getId();

            return !array_key_exists($entrantId, $ranksByEntrantId);
        });

        // Split the combined results into two groups based on if the rank is higher or lower than the highest rank in the new results.
        $higher = [];
        $lower = [];

        foreach ($this->combinedResults as $result) {
            if ($result->getRank() < $highestRank) {
                $higher[] = $result;
            } else {
                $lower[] = $result;
            }
        }

        $results = $this->normalizeResults($results);

        foreach ($results as $result) {
            $result->setRank($result->getRank() + count($higher));
        }

        $lower = $this->normalizeResults($lower);

        foreach ($lower as $result) {
            $result->setRank($result->getRank() + count($higher) + count($results));
        }

        $this->combinedResults = array_merge($higher, $results, $lower);
    }

    /**
     * Find out the previous rank of the player with the highest rank in the given group of results.
     *
     * @param Result[] $results
     * @return int
     */
    protected function getHighestRank(array $results)
    {
        $ranksByEntrantId = $this->getRanksByEntrantId($this->combinedResults);
        $highestRank = null;

        foreach ($results as $result) {
            $entrantId = $result->getEntrant()->getId();

            if (!array_key_exists($entrantId, $ranksByEntrantId)) {
                continue;
            }

            return $ranksByEntrantId[$entrantId];
        }

        return 0;
    }

    /**
     * @param Result[] $results
     * @return array
     */
    protected function getRanksByEntrantId(array $results)
    {
        $resultsByEntrantId = [];

        foreach ($results as $result) {
            $entrantId = $result->getEntrant()->getId();
            $resultsByEntrantId[$entrantId] = $result->getRank();
        }

        return $resultsByEntrantId;
    }
}
