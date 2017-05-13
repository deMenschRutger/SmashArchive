<?php

declare(strict_types = 1);

namespace Domain\Handler\Player;

use CoreBundle\Entity\Set;
use CoreBundle\Repository\SetRepository;
use Domain\Command\Player\HeadToHeadCommand;
use Domain\Handler\AbstractHandler;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class HeadToHeadHandler extends AbstractHandler
{
    /**
     * @param HeadToHeadCommand $command
     * @return array
     */
    public function handle(HeadToHeadCommand $command)
    {
        $playerOneSlug = $command->getPlayerOneSlug();
        $playerTwoSlug = $command->getPlayerTwoSlug();

        /** @var SetRepository $repository */
        $repository = $this->getRepository('CoreBundle:Set');
        $sets = $repository->findHeadToHeadSets($playerOneSlug, $playerTwoSlug);

        $playerOneScore = 0;
        $playerTwoScore = 0;

        foreach ($sets as $set) {
            /** @var Set $set */

            if ($set->getWinner() === null) {
                // This can happen if the result of a set was never submitted.
                continue;
            }

            $winnerSlug = $set->getWinner()->getPlayers()->first()->getSlug();

            if ($winnerSlug == $playerOneSlug) {
                $playerOneScore += 1;
            } elseif ($winnerSlug == $playerTwoSlug) {
                $playerTwoScore += 1;
            }
        }

        return [
            $playerOneSlug => $playerOneScore,
            $playerTwoSlug => $playerTwoScore,
        ];
    }
}
