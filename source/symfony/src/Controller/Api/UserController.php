<?php

declare(strict_types = 1);

namespace App\Controller\Api;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Facebook\Facebook;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration as Sensio;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 *
 * @Sensio\Route("/api/users")
 */
class UserController extends AbstractController
{
    /**
     * @var EntityManagerInterface
     */
    protected $entityManager;

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
            'providerHash' => 'facebook_'.$graphUser->getId(),
        ]);

        if (!$user instanceof User) {
            $user = new User();
            $user->setUsername($graphUser->getName());
            $user->setProvider('facebook');
            $user->setProviderId($graphUser->getId());
            $user->setProviderHash();

            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        return [
            'accessToken' => $this->jwtManager->create($user),
        ];
    }

    /**
     * @return User
     *
     * @Sensio\Route("/me/")
     * @Sensio\Method("GET")
     */
    public function me(): User
    {
        $user = $this->getUser();

        if (!$user instanceof User) {
            throw new NotFoundHttpException('The user could not be found.');
        }

        return $user;
    }
}