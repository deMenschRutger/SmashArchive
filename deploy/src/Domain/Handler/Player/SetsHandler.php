<?php

declare(strict_types = 1);

namespace Domain\Handler\Player;

use CoreBundle\Repository\SetRepository;
use Domain\Command\Player\SetsCommand;
use Domain\Handler\AbstractHandler;
use Knp\Component\Pager\Pagination\PaginationInterface;
use Knp\Component\Pager\Paginator;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class SetsHandler extends AbstractHandler
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
     * @param SetsCommand $command
     * @return PaginationInterface
     */
    public function handle(SetsCommand $command)
    {
        $slug = $command->getSlug();
        $page = $command->getPage();
        $limit = $command->getLimit();

        /** @var SetRepository $repository */
        $repository = $this->getRepository('CoreBundle:Set');
        $query = $repository->findByPlayerSlug($slug);

        return $this->paginator->paginate($query, $page, $limit);
    }
}
