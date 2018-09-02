<?php

declare(strict_types = 1);

namespace App\Bus\Handler;

use App\Bus\Command\Player\OverviewCommand;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\PaginatorInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class PlayerHandler extends AbstractHandler
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
}
