<?php


namespace Ailove\AbstractSocialBundle\Classes;


use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;


abstract class AbstractEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {

        if ($this->supportsToken($authException->getExtraInformation())) {


            $proxy = $this->getSessionProxy();

            return new RedirectResponse($proxy->getAuthUri());

        }

        throw $authException;

    }


    /**
     * implement this method to get your oauth session proxy service
     * @return AbstractSessionProxy
     */
    abstract protected function getSessionProxy();

    /**
     * Return true if your entry point supports token of this social network
     * @param TokenInterface $token
     * @return boolean
     */
    abstract protected function supportsToken(TokenInterface $token);

}
