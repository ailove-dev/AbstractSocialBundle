<?php

namespace Ailove\AbstractSocialBundle\Classes;

use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

class SocialToken extends AbstractToken
{

    /**
     * Constructor.
     *
     * @param string $uid   User ID or name
     * @param array  $roles User roles
     */
    public function __construct($uid = '', array $roles = array())
    {
        parent::__construct($roles);

        $this->setUser(is_object($uid) ? $uid : (string)$uid);

        if (!empty($uid)) {
            $this->setAuthenticated(true);
        }
    }

    /**
     * Return user's credentials.
     *
     * @return string
     */
    public function getCredentials()
    {
        return '';
    }
}
