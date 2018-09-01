<?php

declare(strict_types = 1);

namespace App\Bus\Handler\Event;

use App\Bus\Command\Event\GenerateRanksCommand;
use App\Bus\Handler\AbstractHandler;
use App\Bracket\SingleElimination\Bracket as SingleEliminationBracket;
use App\Bracket\DoubleElimination\Bracket as DoubleEliminationBracket;
use App\Bracket\RoundRobin\Bracket as RoundRobinBracket;
use App\Entity\Event;
use App\Entity\Phase;
use App\Entity\PhaseGroup;
use App\Entity\Rank;
use App\Repository\EventRepository;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class GenerateRanksHandler extends AbstractHandler
{
    /**
     * @var Rank[]
     */
    protected $combinedRanks = [];

    /**
     * @param GenerateRanksCommand $command
     */
    public function handle(GenerateRanksCommand $command): void
    {
        $this->entityManager->getConfiguration()->setSQLLogger(null);
        $eventId = $command->getEventId();

        $this->setIo($command->getIo());
        $this->io->writeln(sprintf('Generating rankings for event #%s...', $eventId));

        /** @var EventRepository $eventRepository */
        $eventRepository = $this->getRepository('App:Event');
        /** @var Event $event */
        $event = $eventRepository->find($eventId);

        if (!$event instanceof Event) {
            throw new \InvalidArgumentException('The event could not be found.');
        }

        $eventRepository->deleteRanks($event);
        $phases = $eventRepository->getOrderedPhases($command->getEventId());

        if (count($phases) === 0) {
            $this->io->writeln('Could not find any phases for the event, skipping...');

            return;
        }

        $this->combinedRanks = $this->getPhaseRanks($event, array_shift($phases));
        $this->combinedRanks = $this->normalizeRanks($this->combinedRanks);

        /** @var Phase $phase */
        foreach ($phases as $phase) {
            $phaseRanks = $this->getPhaseRanks($event, $phase);
            $phaseHighestRank = $this->getHighestRank($phaseRanks);

            $this->processRanks($phaseRanks, $phaseHighestRank);
        }

        foreach ($this->combinedRanks as $rank) {
            $this->entityManager->persist($rank);
        }

        $this->io->writeln('Flushing entity manager...');
        $this->entityManager->flush();

        $this->io->writeln('Counting confirmed players for the tournament...');
        $event->getTournament()->setPlayerCount();

        $this->io->writeln('Counting unique entrants...');
        $entrantCount = $eventRepository->countUniqueEntrants($command->getEventId());
        $event->setEntrantCount($entrantCount);

        $this->io->writeln('Flushing entity manager...');
        $this->entityManager->flush();
        $this->entityManager->clear();
    }

    /**
     * @param Event $event
     * @param Phase $phase
     *
     * @return array
     */
    protected function getPhaseRanks(Event $event, Phase $phase): array
    {
        $ranks = [];

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

            $ranks = array_merge($ranks, $bracket->getRanks($event));
        }

        // Occasionally it might happen that data was incorrectly entered and a player exists in a phase multiple times. Here we filter
        // out the duplicate ranks for those players.
        $existingEntrants = [];

        /** @var Rank $rank */
        foreach ($ranks as $key => $rank) {
            $entrantId = $rank->getEntrant()->getId();

            if (in_array($entrantId, $existingEntrants)) {
                unset($ranks[$key]);
                continue;
            }

            $existingEntrants[] = $entrantId;
        }

        return $ranks;
    }

    /**
     * This method makes sure that entrants in the same rank group receive the same rank, and that no gaps exist between two rank
     * groups.
     *
     * @param Rank[] $ranks
     *
     * @return Rank[]
     */
    protected function normalizeRanks(array $ranks): array
    {
        $entitiesPerRank = [];

        foreach ($ranks as $entity) {
            $rank = $entity->getRank();

            if (!array_key_exists($rank, $entitiesPerRank)) {
                $entitiesPerRank[$rank] = [];
            }

            $entitiesPerRank[$rank][] = $entity;
        }

        ksort($entitiesPerRank);

        $normalizedRanks = [];
        $currentRank = 1;

        foreach ($entitiesPerRank as $rank => $rankEntities) {
            /** @var Rank $entity */
            foreach ($rankEntities as $entity) {
                $entity->setRank($currentRank);
                $normalizedRanks[] = $entity;
            }

            $currentRank += count($rankEntities);
        }

        return $normalizedRanks;
    }

    /**
     * This method adds ranks for a new phase to the ranks of existing phases.
     *
     * @param Rank[] $ranks
     * @param int    $highestRank
     */
    protected function processRanks(array $ranks, int $highestRank): void
    {
        $ranks = $this->normalizeRanks($ranks);

        // Filter out all entrants that are in the new rank group from the combined ranks group.
        $ranksByEntrantId = $this->getRanksByEntrantId($ranks);

        $this->combinedRanks = array_filter($this->combinedRanks, function (Rank $rank) use ($ranksByEntrantId) {
            $entrantId = $rank->getEntrant()->getId();

            return !array_key_exists($entrantId, $ranksByEntrantId);
        });

        // Split the combined ranks into two groups based on if the rank is higher or lower than the highest rank in the new ranks.
        $higher = [];
        $lower = [];

        foreach ($this->combinedRanks as $rank) {
            if ($rank->getRank() < $highestRank) {
                $higher[] = $rank;
            } else {
                $lower[] = $rank;
            }
        }

        $ranks = $this->normalizeRanks($ranks);

        foreach ($ranks as $rank) {
            $rank->setRank($rank->getRank() + count($higher));
        }

        $lower = $this->normalizeRanks($lower);

        foreach ($lower as $rank) {
            $rank->setRank($rank->getRank() + count($higher) + count($ranks));
        }

        $this->combinedRanks = array_merge($higher, $ranks, $lower);
    }

    /**
     * Find out the previous rank of the player with the highest rank in the given group of ranks.
     *
     * @param Rank[] $ranks
     *
     * @return int
     */
    protected function getHighestRank(array $ranks): int
    {
        $ranksByEntrantId = $this->getRanksByEntrantId($this->combinedRanks);

        foreach ($ranks as $rank) {
            $entrantId = $rank->getEntrant()->getId();

            if (!array_key_exists($entrantId, $ranksByEntrantId)) {
                continue;
            }

            return $ranksByEntrantId[$entrantId];
        }

        return 0;
    }

    /**
     * @param Rank[] $ranks
     *
     * @return array
     */
    protected function getRanksByEntrantId(array $ranks): array
    {
        $ranksByEntrantId = [];

        foreach ($ranks as $rank) {
            $entrantId = $rank->getEntrant()->getId();
            $ranksByEntrantId[$entrantId] = $rank->getRank();
        }

        return $ranksByEntrantId;
    }
}
