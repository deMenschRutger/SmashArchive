<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route("/api")
 */
class DefaultController
{
    /**
     * @return array
     *
     * @Route("/")
     */
    public function index(): array
    {
        return [
            'version' => 0.1,
        ];
    }
}
