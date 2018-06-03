<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Route("/api/v0.1/users")
 */
class UserController
{
    /**
     * @return array
     *
     * @Route("/login/")
     */
    public function login(): array
    {
        return [
            'accessToken' => 'customAccessToken',
        ];
    }
}
