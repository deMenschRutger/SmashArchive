<?php

declare(strict_types=1);

namespace Domain\Handler\Player;

use CoreBundle\Entity\Set;
use CoreBundle\Repository\SetRepository;
use Doctrine\ORM\EntityManager;
use Domain\Command\HeadToHeadCommand;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class HeadToHeadHandler
{
    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @return EntityManager
     */
    public function getEntityManager(): EntityManager
    {
        return $this->entityManager;
    }

    /**
     * @param EntityManager $entityManager
     */
    public function setEntityManager(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param HeadToHeadCommand $command
     * @return array
     */
    public function handle(HeadToHeadCommand $command)
    {
        $playerOneId = $command->getPlayerOneId();
        $playerTwoId = $command->getPlayerTwoId();

        /** @var SetRepository $repository */
        $repository = $this->getEntityManager()->getRepository('CoreBundle:Set');
        $sets = $repository->findHeadToHeadSets($playerOneId, $playerTwoId);

        $playerOneScore = 0;
        $playerTwoScore = 0;

        foreach ($sets as $set) {
            /** @var Set $set */
            $winnerId = $set->getWinner()->getPlayers()->first()->getId();

            if ($winnerId == $playerOneId) {
                $playerOneScore += 1;
            } elseif ($winnerId == $playerTwoId) {
                $playerTwoScore += 1;
            }
        }

        return [
            $playerOneId => $playerOneScore,
            $playerTwoId => $playerTwoScore,
        ];
    }
}
