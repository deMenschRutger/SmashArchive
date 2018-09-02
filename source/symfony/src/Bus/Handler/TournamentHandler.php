<?php

declare(strict_types = 1);

namespace App\Bus\Handler;

use App\Bus\Command\Tournament\DetailsCommand;
use App\Bus\Command\Tournament\OverviewCommand;
use App\Bus\Command\Tournament\RanksCommand;
use App\Entity\Rank;
use App\Entity\Tournament;
use App\Repository\RankRepository;
use App\Repository\TournamentRepository;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
final class TournamentHandler extends AbstractHandler
{
    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * @param PaginatorInterface $paginator
     */
    public function __construct(PaginatorInterface $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @param OverviewCommand $command
     *
     * @return PaginationInterface
     */
    public function handleOverviewCommand(OverviewCommand $command)
    {
        $name = $command->getName();
        $location = $command->getLocation();
        $page = $command->getPage();
        $limit = $command->getLimit();

        $queryBuilder = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('t, c')
            ->from('App:Tournament', 't')
            ->leftJoin('t.country', 'c')
            ->where('t.isActive = :isActive')
            ->orderBy('t.'.$command->getSort(), $command->getOrder())
            ->setParameter('isActive', true)
        ;

        if ($name) {
            $queryBuilder->andWhere('t.name LIKE :name')->setParameter('name', "%{$name}%");
        }

        if ($location) {
            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->like('c.name', ':location'),
                $queryBuilder->expr()->like('t.region', ':location'),
                $queryBuilder->expr()->like('t.city', ':location')
            ))->setParameter('location', "%{$location}%");
        }

        return $this->paginator->paginate($queryBuilder->getQuery(), $page, $limit);
    }

    /**
     * @param DetailsCommand $command
     *
     * @return Tournament
     */
    public function handleDetailsCommand(DetailsCommand $command)
    {
        /** @var TournamentRepository $tournamentRepository */
        $tournamentRepository = $this->getRepository('App:Tournament');
        $tournament = $tournamentRepository->findWithDetails($command->getSlug());

        if (!$tournament instanceof Tournament) {
            throw new NotFoundHttpException('The tournament could not be found.');
        }

        return $tournament;
    }

    /**
     * @param RanksCommand $command
     *
     * @return array
     */
    public function handleRanksCommand(RanksCommand $command)
    {
        /** @var RankRepository $rankRepository */
        $rankRepository = $this->getRepository('App:Rank');
        $tournamentId = $command->getTournamentId();
        $eventId = $command->getEventId();

        if ($tournamentId) {
            $ranksPerEvent = [];
            $ranks = $rankRepository->findForTournament($tournamentId);

            /** @var Rank $rank */
            foreach ($ranks as $rank) {
                $eventId = $rank->getEvent()->getId();

                if (!array_key_exists($eventId, $ranksPerEvent)) {
                    $ranksPerEvent[$eventId] = [];
                }

                $ranksPerEvent[$eventId][] = $rank;
            }

            return $ranksPerEvent;
        } elseif ($eventId) {
            return $rankRepository->findForEvent($eventId);
        }

        return [];
    }
}
