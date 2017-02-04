<?php

declare(strict_types=1);

namespace ApiBundle\Controller;

use CoreBundle\Controller\AbstractDefaultController;
use Domain\Command\HeadToHeadCommand;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @Route(service="api.controller.default")
 *
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class DefaultController extends AbstractDefaultController
{
    /**
     * @Route("/")
     */
    public function indexAction()
    {
        return [];
    }

    /**
     * @param int $playerOneId
     * @param int $playerTwoId
     * @return array
     *
     * @Route("/head-to-head/{playerOneId}/{playerTwoId}")
     *
     * @TODO Add route requirements.
     * @TODO Use slugs instead of IDs?
     */
    public function headToHeadAction($playerOneId, $playerTwoId)
    {
        $command = new HeadToHeadCommand($playerOneId, $playerTwoId);

        return $this->commandBus->handle($command);
    }
}
