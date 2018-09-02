<?php

declare(strict_types = 1);

namespace App\Bus\Handler;

use App\Bus\Command\Player\OverviewCommand;
use App\Bus\Command\Player\SetsCommand;
use App\Entity\Set;
use App\Repository\SetRepository;
use Doctrine\ORM\Query;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
final class PlayerHandler extends AbstractHandler
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
            // TODO Add option to filter by even type.
            $query = $repository->findByProfileSlug($slug, 'all');
        }

        if ($command->getSortByPhase()) {
            return $this->getSetsByPhaseId($query);
        }

        return $this->paginator->paginate($query, $page, $limit);
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
}
