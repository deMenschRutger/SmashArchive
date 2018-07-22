<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Entity\User;
use Facebook\Facebook;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;
use Symfony\Component\HttpFoundation\Request;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Sensio\Route("/api/v0.1/users")
 */
class UserController extends AbstractController
{
    /**
     * @var JWTTokenManagerInterface
     */
    private $jwtManager;

    /**
     * @var Facebook
     */
    private $facebook;

    /**
     * @param JWTTokenManagerInterface $jwtManager
     * @param Facebook                 $facebook
     */
    public function __construct(JWTTokenManagerInterface $jwtManager, Facebook $facebook)
    {
        $this->jwtManager = $jwtManager;
        $this->facebook = $facebook;
    }

    /**
     * @param Request $request
     *
     * @return array
     *
     * @Sensio\Route("/login/")
     * @Sensio\Method("POST")
     */
    public function login(Request $request): array
    {
        $fbAccessToken = $request->get('accessToken');
        $graphUser = $this->facebook->get('/me', $fbAccessToken)->getGraphUser();

        $user = new User();
        $user->setUsername($graphUser->getName());

        return [
            'accessToken' => $this->jwtManager->create($user),
        ];
    }

    /**
     * @return array
     *
     * @Sensio\Route("/me/")
     * @Sensio\Method("GET")
     */
    public function me(): array
    {
        return [
            'username' => $this->getUser()->getUsername(),
        ];
    }
}
