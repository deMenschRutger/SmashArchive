<?php

declare(strict_types = 1);

namespace CoreBundle\Security\Core\User\HWIOAuthUserProvider\ResourceAdapter;

use HWI\Bundle\OAuthBundle\OAuth\Response\UserResponseInterface;

/**
 * @author Rutger Mensch <rutger@rutgermensch.com>
 */
class Factory
{
    /**
     * @param UserResponseInterface $response
     * @return AbstractAdapter
     */
    public static function createResourceAdapter(UserResponseInterface $response)
    {
        $service = $response->getResourceOwner()->getName();

        switch ($service) {
            case 'facebook':
                return new FacebookAdapter($response);
                break;

            case 'twitter':
                return new TwitterAdapter($response);
                break;
        }

        throw new \InvalidArgumentException(
            "Could not find a corresponding resource adapter for the name '{$service}'."
        );
    }
}
