<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
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
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @var JWTTokenManagerInterface
     */
    private $jwtManager;

    /**
     * @var Facebook
     */
    private $facebook;

    /**
     * @param EntityManagerInterface   $entityManager
     * @param JWTTokenManagerInterface $jwtManager
     * @param Facebook                 $facebook
     */
    public function __construct(
        EntityManagerInterface $entityManager,
        JWTTokenManagerInterface $jwtManager,
        Facebook $facebook
    ) {
        $this->entityManager = $entityManager;
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

        $user = $this->getRepository('App:User')->findOneBy([
            'provider'   => 'facebook',
            'providerId' => $graphUser->getId(),
        ]);

        if (!$user instanceof User) {
            $user = new User();
            $user->setUsername($graphUser->getName());
            $user->setProvider('facebook');
            $user->setProviderId($graphUser->getId());

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

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
