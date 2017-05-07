<?php

declare(strict_types = 1);

namespace CoreBundle\Security\Core\User\HWIOAuthUserProvider\ResourceAdapter;

use CoreBundle\Entity\User;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class FacebookAdapter extends AbstractAdapter
{
    /**
     * @return array
     */
    public function getFindCriteria()
    {
        return [ 'facebookId' => $this->response->getUsername() ];
    }

    /**
     * @param User $user
     */
    public function setUserAuthData(User $user)
    {
        $user->setFacebookId($this->response->getUsername());
        $user->setFacebookAccessToken($this->response->getAccessToken());
    }

    /**
     * @param User $user
     */
    public function resetUserAuthData(User $user)
    {
        $user->setFacebookId(null);
        $user->setFacebookAccessToken(null);
    }

    /**
     * @param User $user
     */
    public function createUser(User $user)
    {
        $fullName = sprintf('%s %s', $this->response->getFirstName(), $this->response->getLastName());

        $this->setUserAuthData($user);
        $user->setUsername($fullName);
        $user->setEnabled(true);
    }
}
