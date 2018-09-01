<?php

declare(strict_types = 1);

namespace App\Bus\Handler;

use App\Bus\Command\Tournament\OverviewCommand;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class TournamentHandler extends AbstractHandler
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
     * @return PaginationInterface
     */
    public function handle(OverviewCommand $command)
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
}
