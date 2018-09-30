<?php

declare(strict_types = 1);

namespace App\Bus\Handler;

use App\Bus\Command\Profile\DetailsCommand;
use App\Bus\Command\Profile\HeadToHeadCommand;
use App\Bus\Command\Profile\OverviewCommand;
use App\Bus\Command\Profile\RanksCommand;
use App\Bus\Command\Profile\SetsCommand;
use App\Entity\Entrant;
use App\Entity\Event;
use App\Entity\Profile;
use App\Entity\Rank;
use App\Entity\Set;
use App\Repository\EntrantRepository;
use App\Repository\ProfileRepository;
use App\Repository\RankRepository;
use App\Repository\SetRepository;
use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
final class ProfileHandler extends AbstractHandler
{
    /**
     * @var PaginatorInterface
     */
    protected $paginator;

    /**
     * @var array
     */
    protected $events = [];

    /**
     * @var array
     */
    protected $setsByEventId = [];

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
        $page = $command->getPage();
        $location = $command->getLocation();
        $limit = $command->getLimit();
        $tag = $command->getTag();

        $queryBuilder = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('p, c')
            ->from('App:Profile', 'p')
            ->leftJoin('p.country', 'c')
            ->orderBy('p.gamerTag')
        ;

        if ($tag) {
            $queryBuilder->andWhere('p.gamerTag LIKE :tag')->setParameter('tag', "%{$tag}%");
        }

        if ($location) {
            $queryBuilder->andWhere($queryBuilder->expr()->orX(
                $queryBuilder->expr()->like('c.name', ':location'),
                $queryBuilder->expr()->like('p.region', ':location'),
                $queryBuilder->expr()->like('p.city', ':location')
            ))->setParameter('location', "%{$location}%");
        }

        return $this->paginator->paginate($queryBuilder->getQuery(), $page, $limit);
    }

    /**
     * @param DetailsCommand $command
     *
     * @return Profile
     */
    public function handleDetailsCommand(DetailsCommand $command)
    {
        $profile = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('p, c, m, s, gm, sm')
            ->from('App:Profile', 'p')
            ->leftJoin('p.country', 'c')
            ->leftJoin('p.mains', 'm')
            ->leftJoin('m.game', 'gm')
            ->leftJoin('p.secondaries', 's')
            ->leftJoin('s.game', 'sm')
            ->where('p.slug = :slug')
            ->setParameter('slug', $command->getSlug())
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if (!$profile instanceof Profile) {
            throw new NotFoundHttpException('The player could not be found.');
        }

        return $profile;
    }

    /**
     * @param SetsCommand $command
     *
     * @return PaginationInterface|array
     */
    public function handleSetsCommand(SetsCommand $command)
    {
        $slug = $command->getSlug();
        $eventId = $command->getEventId();
        $page = $command->getPage();
        $limit = $command->getLimit();

        /** @var SetRepository $repository */
        $repository = $this->getRepository('App:Set');

        if ($eventId) {
            $query = $repository->findByProfileSlugAndEventId($slug, $eventId);
        } else {
            // TODO Add option to filter by event type.
            $query = $repository->findByProfileSlug($slug, 'all');
        }

        if ($command->getSortByPhase()) {
            return $this->getSetsByPhaseId($query);
        }

        return $this->paginator->paginate($query, $page, $limit);
    }

    /**
     * @param RanksCommand $command
     *
     * @return array
     */
    public function handleRanksCommand(RanksCommand $command)
    {
        $slug = $command->getProfileSlug();
        $eventId = $command->getEventId();

        /** @var EntrantRepository $entrantRepository */
        $entrantRepository = $this->getRepository('App:Entrant');
        $entrants = $entrantRepository->findByProfileSlug($slug, $eventId);

        /** @var RankRepository $rankRepository */
        $rankRepository = $this->getRepository('App:Rank');
        $ranks = $rankRepository->findForProfile($slug, $eventId);

        $entities = [];

        foreach ($entrants as $entrant) {
            $entity = new Rank();
            $entity->setEntrant($entrant);

            $event = $entrant->getOriginEvent();

            if ($event instanceof Event) {
                $rank = $this->findRank($entrant, $event, $ranks);

                $entity->setEvent($event);
                $entity->setRank($rank);
            }

            $entities[] = $entity;
        }

        return $entities;
    }

    /**
     * @param HeadToHeadCommand $command
     *
     * @return array
     */
    public function handleHeadToHeadCommand(HeadToHeadCommand $command)
    {
        $profileOneSlug = $command->getProfileOneSlug();
        $profileTwoSlug = $command->getProfileTwoSlug();

        /** @var ProfileRepository $profileRepository */
        $profileRepository = $this->getRepository('App:Profile');

        if (!$profileRepository->exists($profileOneSlug)) {
            throw new NotFoundHttpException("The first profile could not be found.");
        }

        if (!$profileRepository->exists($profileTwoSlug)) {
            throw new NotFoundHttpException("The second profile could not be found.");
        }

        /** @var SetRepository $setRepository */
        $setRepository = $this->getRepository('App:Set');
        $sets = $setRepository->findHeadToHeadSets($profileOneSlug, $profileTwoSlug);

        $profileOneScore = 0;
        $profileTwoScore = 0;

        foreach ($sets as $set) {
            /** @var Set $set */

            if ($set->getWinner() === null) {
                // This can happen if the result of a set was never submitted or the set was never played.
                continue;
            }

            $winnerSlug = $set->getWinner()->getPlayers()->first()->getSlug();

            if ($winnerSlug == $profileOneSlug) {
                $profileOneScore += 1;
            } elseif ($winnerSlug == $profileTwoSlug) {
                $profileTwoScore += 1;
            }
        }

        return [
            $profileOneSlug => $profileOneScore,
            $profileTwoSlug => $profileTwoScore,
        ];
    }

    /**
     * @param Query $query
     *
     * @return array
     */
    protected function getSetsByPhaseId(Query $query)
    {
        $sets = $query->getResult();
        $setsByPhaseId = [];

        /** @var Set[] $sets */
        foreach ($sets as $set) {
            $phaseGroup = $set->getPhaseGroup();
            $phase = $phaseGroup->getPhase();
            $phaseId = $phase->getId();

            if (!array_key_exists($phaseId, $setsByPhaseId)) {
                $setsByPhaseId[$phaseId] = [
                    'name' => $phase->getName(),
                    'sets'  => [],
                ];
            }

            $setsByPhaseId[$phaseId]['sets'][] = $set;
        }

        return $setsByPhaseId;
    }

    /**
     * @param Entrant  $entrant
     * @param Event    $event
     * @param Rank[]   $ranks
     *
     * @return int|null
     */
    protected function findRank(Entrant $entrant, Event $event, array $ranks)
    {
        foreach ($ranks as $rank) {
            if ($rank->getEntrant() === $entrant && $rank->getEvent() === $event) {
                return $rank->getRank();
            }
        }

        return null;
    }
}
