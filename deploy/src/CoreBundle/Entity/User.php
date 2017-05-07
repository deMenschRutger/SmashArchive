<?php

declare(strict_types = 1);

namespace CoreBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="users")
 *
 * @ORM\AttributeOverrides({
 *  @ORM\AttributeOverride(name="password",
 *      column=@ORM\Column(nullable = true)
 *  ),
 *  @ORM\AttributeOverride(name="email",
 *      column=@ORM\Column(nullable = true)
 *  ),
 *  @ORM\AttributeOverride(name="emailCanonical",
 *      column=@ORM\Column(nullable = true)
 *  )
 * })
 */
class User extends BaseUser
{
    const AUTH_PROVIDER_FACEBOOK = 'facebook';
    const AUTH_PROVIDER_TWITTER  = 'twitter';

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="facebook_id", type="string", length=255, nullable=true)
     */
    protected $facebookId;

    /**
     * @ORM\Column(name="facebook_access_token", type="encrypted", nullable=true)
     */
    protected $facebookAccessToken;

    /**
     * @var string
     *
     * @ORM\Column(name="twitter_id", type="string", length=255, nullable=true)
     */
    protected $twitterId;

    /**
     * @ORM\Column(name="twitter_access_token", type="encrypted", nullable=true)
     */
    protected $twitterAccessToken;

    /**
     *
     */
    public function __construct()
    {
        $this->enabled = false;
        $this->roles = [];
    }

    /**
     * @return string
     */
    public function getFacebookId()
    {
        return $this->facebookId;
    }

    /**
     * @param string $facebookId
     */
    public function setFacebookId($facebookId)
    {
        $this->facebookId = $facebookId;
    }

    /**
     * @return string
     */
    public function getFacebookAccessToken()
    {
        return $this->facebookAccessToken;
    }

    /**
     * @param string $facebookAccessToken
     */
    public function setFacebookAccessToken($facebookAccessToken)
    {
        $this->facebookAccessToken = $facebookAccessToken;
    }

    /**
     * @return string
     */
    public function getTwitterId()
    {
        return $this->twitterId;
    }

    /**
     * @param string $twitterId
     */
    public function setTwitterId($twitterId)
    {
        $this->twitterId = $twitterId;
    }

    /**
     * @return string
     */
    public function getTwitterAccessToken()
    {
        return $this->twitterAccessToken;
    }

    /**
     * @param string $twitterAccessToken
     */
    public function setTwitterAccessToken($twitterAccessToken)
    {
        $this->twitterAccessToken = $twitterAccessToken;
    }

    /**
     * @return string
     */
    public function getAuthProvider()
    {
        switch (true) {
            case $this->facebookId:
                return self::AUTH_PROVIDER_FACEBOOK;

            case $this->twitterId:
                return self::AUTH_PROVIDER_TWITTER;
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function setUsernameCanonical($usernameCanonical)
    {
        $prefix = $this->getAuthProvider();

        if (!$prefix) {
            throw new \InvalidArgumentException("User #{$this->getId()} does not have a valid authentication provider.");
        }

        $this->usernameCanonical = $prefix.'_'.$usernameCanonical;
    }
}
