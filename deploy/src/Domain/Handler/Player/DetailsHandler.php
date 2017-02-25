<?php

declare(strict_types=1);

namespace Domain\Handler\Player;

use CoreBundle\DataTransferObject\PlayerDTO;
use CoreBundle\Entity\Player;
use Domain\Command\Player\DetailsCommand;
use Domain\Handler\AbstractHandler;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class DetailsHandler extends AbstractHandler
{
    /**
     * @param DetailsCommand $command
     * @return PlayerDTO
     */
    public function handle(DetailsCommand $command)
    {
        $player = $this->getRepository('CoreBundle:Player')->findOneBy([
            'slug' => $command->getSlug(),
        ]);

        if (!$player instanceof Player) {
            throw new NotFoundHttpException();
        }

        return new PlayerDTO($player);
    }
}
