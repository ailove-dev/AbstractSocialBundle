<?php


namespace Ailove\AbstractSocialBundle\Classes;

use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

use FOS\UserBundle\Model\UserManagerInterface;
use Application\Sonata\UserBundle\Entity\User;
use Ailove\AbstractSocialBundle\Classes\AbstractSessionProxy;

abstract class AbstractUserProvider implements UserProviderInterface
{
    /**
     * @var AbstractSessionProxy
     */
    protected $oauthProxy;

    /**
     * @var UserManagerInterface
     */
    protected $userManager;

    /**
     * Constructor.
     *
     * @param AbstractSessionProxy $oauthProxy  Oauth2Proxy instance
     * @param UserManagerInterface $userManager User manager
     */
    public function __construct(AbstractSessionProxy $oauthProxy, UserManagerInterface $userManager)
    {
        $this->oauthProxy = $oauthProxy;
        $this->userManager = $userManager;
    }

    /**
     * Check class support.
     *
     * @param string $class Classname
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $this->userManager->supportsClass($class);
    }

    /**
     * Find user by social uid.
     *
     * @param string $uid user social uid
     * @return \FOS\UserBundle\Model\UserInterface
     * @throws UsernameNotFoundException
     *
     */
    abstract public function findUserByUid($uid);

    /**
     * Load user by username.
     *
     * @param string $username In fact is a social uid
     *
     * @return \FOS\UserBundle\Model\UserInterface
     *
     * @throws \Symfony\Component\Security\Core\Exception\UsernameNotFoundException
     */
    public function loadUserByUsername($username)
    {
        $user = $this->findUserByUid($username);

        if (empty($user))
            throw new UsernameNotFoundException('User with uid ' . $username . ' can not be found');

        return $user;
//        if (empty($user)) {
//            try {
//                $this->oauthProxy->authorize();
//                $uid = $this->oauthProxy->getUserId();
//
//            } catch (\Exception $e) {
//                $uid = null;
//            }
//
//            if (!empty($uid)) {
//                $user = new User();
//                $user->setEnabled(true); // Temporary enable user - to access connect page
//                $user->setPassword('');
//                $user->setUsername($uid);
//                $user->setEmail($uid . '@facebook.com');
//                $user->setOkUid($uid);
//                $user->addRole(User::ROLE_FACEBOOK_USER);
////                $this->userManager->updateUser($user, false);
//            }
//        }
//
//        if (empty($user)) {
//            throw new UsernameNotFoundException('The user is not authenticated on Facebook');
//        }

    }

    /**
     * Refresh user.
     *
     * @param \Symfony\Component\Security\Core\User\UserInterface $user User instance
     *
     * @return \Application\Sonata\UserBundle\Entity\User|\FOS\UserBundle\Model\UserInterface
     *
     * @throws \Symfony\Component\Security\Core\Exception\UnsupportedUserException
     */
    public function refreshUser(UserInterface $user)
    {
        if (!$this->supportsClass(get_class($user)) || !$this->getUserUid($user)) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($this->getUserUid($user));
    }

    /**
     * Implement this to return uid for concrete social network
     * Throw a UsernameNotFoundException if user is not supported
     * @param UserInterface $user
     * @throws UnsupportedUserException
     * @return string
     */
    abstract public function getUserUid(UserInterface $user);

    /**
     * Implement this method to return a role of the authorized user (this need to add this role to the token)
     * @return string
     */
//    abstract public function getSocialRole();
}
