<?php

declare(strict_types = 1);

namespace CoreBundle\Security\Core\User\HWIOAuthUserProvider\ResourceAdapter;

use CoreBundle\Entity\User;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class TwitterAdapter extends AbstractAdapter
{
    /**
     * @return array
     */
    public function getFindCriteria()
    {
        return [ 'twitterId' => $this->response->getUsername() ];
    }

    /**
     * @param User $user
     */
    public function setUserAuthData(User $user)
    {
        $user->setTwitterId($this->response->getUsername());
        $user->setTwitterAccessToken($this->response->getAccessToken());
    }

    /**
     * @param User $user
     */
    public function resetUserAuthData(User $user)
    {
        $user->setTwitterId(null);
        $user->setTwitchAccessToken(null);
    }

    /**
     * @param User $user
     */
    public function createUser(User $user)
    {
        $this->setUserAuthData($user);
        $user->setUsername($this->response->getRealName());
        $user->setEnabled(true);
    }
}
