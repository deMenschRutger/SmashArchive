<?php

declare(strict_types = 1);

namespace CoreBundle\Security\Core\User\HWIOAuthUserProvider;

use CoreBundle\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;
use HWI\Bundle\OAuthBundle\Security\Core\User\FOSUBUserProvider as BaseClass;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 * @link https://gist.github.com/danvbe/4476697
 */
class UserProvider extends BaseClass
{
    /**
     * {@inheritDoc}
     */
    public function connect(UserInterface $user, UserResponseInterface $response)
    {
        $adapter = ResourceAdapter\Factory::createResourceAdapter($response);

        // Check if a previously connected user needs to be reset.
        $previousUser = $this->userManager->findUserBy($adapter->getFindCriteria());

        if ($previousUser instanceof User) {
            $adapter->resetUserAuthData($previousUser);
            $this->userManager->updateUser($previousUser);
        }

        /** @var User $user */
        $adapter->setUserAuthData($user);

        $this->userManager->updateUser($user);
    }

    /**
     * {@inheritdoc}
     */
    public function loadUserByOAuthUserResponse(UserResponseInterface $response)
    {
        $adapter = ResourceAdapter\Factory::createResourceAdapter($response);

        /** @var User $user */
        $user = $this->userManager->findUserBy($adapter->getFindCriteria());

        // The user doesn't exist yet.
        if (!$user instanceof User) {
            $user = $this->userManager->createUser();

            $adapter->createUser($user);

            $this->userManager->updateUser($user);

            return $user;
        }

        // The user exists, update the access token.
        $adapter->setUserAuthData($user);

        return $user;
    }
}
