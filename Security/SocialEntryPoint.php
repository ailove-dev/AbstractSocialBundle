<?php


namespace Ailove\AbstractSocialBundle\Security;


use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Ailove\AbstractSocialBundle\Classes\AbstractEntryPoint;


class SocialEntryPoint implements AuthenticationEntryPointInterface
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var \Ailove\AbstractSocialBundle\Classes\AbstractEntryPoint[]
     */
    protected $entryPoints;

    function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->entryPoints = array();
    }

    /**
     * {@inheritdoc}
     */
    public function start(Request $request, AuthenticationException $authException = null)
    {
        foreach ($this->entryPoints as $entryPoint) {
            try {
                return $entryPoint->start($request, $authException);
            } catch (\Exception $e) {

            }
        }

        throw $authException;
    }

    public function addEntryPoint(AbstractEntryPoint $entryPoint)
    {
        $this->entryPoints[] = $entryPoint;
    }


}
