<?php

declare(strict_types=1);

namespace Domain\Handler\Tournament;

use CoreBundle\DataTransferObject\TournamentDTO;
use Domain\Command\Tournament\OverviewCommand;
use Domain\Handler\AbstractHandler;
use Knp\Bundle\PaginatorBundle\Pagination\SlidingPagination;
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
     * @return array
     */
    public function handle(OverviewCommand $command)
    {
        $name = $command->getName();
        $page = $command->getPage();
        $limit = $command->getLimit();

        $queryBuilder = $this
            ->getEntityManager()
            ->createQueryBuilder()
            ->select('t')
            ->from('CoreBundle:Tournament', 't')
            ->orderBy('t.name')
        ;

        if ($name) {
            $queryBuilder->where('t.name LIKE :name')->setParameter('name', "%{$name}%");
        }

        /** @var SlidingPagination $pagination */
        $pagination = $this->paginator->paginate($queryBuilder->getQuery(), $page, $limit);
        $tournaments = [];

        foreach ($pagination as $tournament) {
            $tournaments[] = new TournamentDTO($tournament);
        }

        return [
            'pagination' => $pagination,
            'tournaments' => $tournaments,
        ];
    }
}
