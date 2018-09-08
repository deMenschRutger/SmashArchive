<?php

declare(strict_types = 1);

namespace Domain\Handler\Player;

use Domain\Command\Player\OverviewCommand;
use Domain\Handler\AbstractHandler;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Paginator;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class OverviewHandler extends AbstractHandler
{
    /**
     * @var Paginator
     */
    protected $paginator;

    /**
     * @param Paginator $paginator
     */
    public function __construct(Paginator $paginator)
    {
        $this->paginator = $paginator;
    }

    /**
     * @param OverviewCommand $command
     * @return PaginationInterface
     */
    public function handle(OverviewCommand $command)
    {
        $page = $command->getPage();
        $location = $command->getLocation();
        $limit = $command->getLimit();
        $tag = $command->getTag();

        $queryBuilder = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('p, c')
            ->from('CoreBundle:PlayerProfile', 'p')
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
}
