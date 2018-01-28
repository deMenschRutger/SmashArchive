<?php

declare(strict_types = 1);

namespace Domain\Handler\Player;

use CoreBundle\Entity\Set;
use CoreBundle\Repository\PlayerProfileRepository;
use CoreBundle\Repository\SetRepository;
use Domain\Command\Player\HeadToHeadCommand;
use Domain\Handler\AbstractHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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

        /** @var PlayerProfileRepository $playerProfileRepository */
        $playerProfileRepository = $this->getRepository('CoreBundle:PlayerProfile');

        if (!$playerProfileRepository->exists($playerOneSlug)) {
            throw new NotFoundHttpException("The player '{$playerOneSlug}' could not be found.");
        }

        if (!$playerProfileRepository->exists($playerTwoSlug)) {
            throw new NotFoundHttpException("The player '{$playerTwoSlug}' could not be found.");
        }

        /** @var SetRepository $setRepository */
        $setRepository = $this->getRepository('CoreBundle:Set');
        $sets = $setRepository->findHeadToHeadSets($playerOneSlug, $playerTwoSlug);

        $playerOneScore = 0;
        $playerTwoScore = 0;

        foreach ($sets as $set) {
            /** @var Set $set */

            if ($set->getWinner() === null) {
                // This can happen if the result of a set was never submitted or the set was never played.
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
