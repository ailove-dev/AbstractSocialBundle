<?php

namespace Ailove\AbstractSocialBundle\Classes;

use Symfony\Component\Security\Core\Authentication\Provider\AuthenticationProviderInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserChecker;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;

use Ailove\AbstractSocialBundle\Classes\AbstractSessionProxy;


abstract class AbstractAuthenticationProvider implements AuthenticationProviderInterface
{


    /**
     * @var AbstractSessionProxy
     */
    private $oauthProxy;

    /**
     * @var UserProviderInterface
     */
    private $userProvider;

    /**
     * @var UserChecker
     */
    protected $userChecker;

    /**
     * Constructor.
     *
     * @param AbstractSessionProxy $oauthProxy oAuth proxy
     * @param UserProviderInterface $userProvider User provider
     * @param UserChecker $userChecker  User checker
     */
    public function __construct(AbstractSessionProxy $oauthProxy, UserProviderInterface $userProvider, UserChecker $userChecker)
    {
        $this->userProvider = $userProvider;
        $this->oauthProxy = $oauthProxy;
        $this->userChecker = $userChecker;
    }

    /**
     * Authenticate user.
     *
     * @param TokenInterface $token Auth token
     *
     * @return SocialToken|null
     *
     * @throws AuthenticationException
     */
    public function authenticate(TokenInterface $token)
    {
        if (!$this->supports($token)) {
            return null;
        }

        try {
            if ($this->oauthProxy->authorize() === true) {

                $authenticatedToken = $this->createAuthenticatedToken($this->oauthProxy->getUserId());

                return $authenticatedToken;
            }
        } catch (AuthenticationException $failed) {
            throw $failed;
        } catch (\Exception $failed) {
            throw new AuthenticationException('auth error: ' . $failed->getMessage(), $failed->getMessage(), $failed->getCode(), $failed);
        }

        throw new AuthenticationException('Не получилось загрузить данные пользователя');
    }

    /**
     * implement this method to return full class name of the token.
     * @return string
     */
    abstract protected function getTokenClass();

    /**
     * Is provider supports $token.
     *
     * @param TokenInterface $token Auth token.
     *
     * @return bool
     */
    public function supports(TokenInterface $token)
    {
        $class = $this->getTokenClass();

        return $token instanceof $class;
    }

    /**
     * Create auth token.
     *
     * @param string $uid
     *
     * @return SocialToken
     *
     * @throws \RuntimeException
     */
    protected function createAuthenticatedToken($uid)
    {

        $tokenClass = $this->getTokenClass();

        if (null === $this->userProvider) {

            return new $tokenClass($uid);
        }

        try {
            $user = $this->userProvider->loadUserByUsername($uid);
        } catch (UsernameNotFoundException $e) {
            $user = null;
        }

        if ($user) {

            $this->userChecker->checkPostAuth($user);

            if (!$user instanceof UserInterface) {
                throw new \RuntimeException('User provider did not return an implementation of user interface.');
            }
        }


        $token = new $tokenClass($user ? $user : $uid, $user ? $user->getRoles() : $this->getDefaultSocialRoles());


        if (!$token instanceof SocialToken)
            throw new AuthenticationException('Token should be instance of SocialToken class');

        return $token;
    }


    /**
     * Should return an array of social roles to add to token. Example: return array('ROLE_SOCIAL_USER');
     * @return array
     */
    abstract protected  function getDefaultSocialRoles();
}
