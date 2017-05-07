<?php

declare(strict_types = 1);

namespace CoreBundle\Security\Core\User\HWIOAuthUserProvider\ResourceAdapter;

use CoreBundle\Entity\User;
use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
abstract class AbstractAdapter
{
    /**
     * @var UserResponseInterface
     */
    protected $response;

    /**
     * @param UserResponseInterface $response
     */
    public function __construct(UserResponseInterface $response)
    {
        $this->response = $response;
    }

    /**
     * @return array
     */
    abstract public function getFindCriteria();

    /**
     * @param User $user
     */
    abstract public function setUserAuthData(User $user);

    /**
     * @param User $user
     */
    abstract public function resetUserAuthData(User $user);

    /**
     * @param User $user
     */
    abstract public function createUser(User $user);
}
