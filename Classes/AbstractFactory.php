<?php

namespace Ailove\AbstractSocialBundle\Classes;

use Symfony\Bundle\SecurityBundle\DependencyInjection\Security\Factory\AbstractFactory as BaseFactory;
use Symfony\Component\DependencyInjection\DefinitionDecorator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

abstract class AbstractFactory extends BaseFactory
{
    /**
     * @param string $id
     * @return string
     */
    protected function generateEntryPointId($id)
    {
        return $this->getServicePrefix() . '.security.authentication.entry_point.' . $id;
    }

    protected function createEntryPoint($container, $id, $config, $defaultEntryPointId)
    {

        $entryPointId = $this->generateEntryPointId($id);

        $container
            ->setDefinition($entryPointId, new DefinitionDecorator('social.security.authentication.entry_point'))
        ;

        return $entryPointId;
    }

    /**
     * Return key.
     *
     * @return string
     */
    public function getKey()
    {
        return $this->getServicePrefix() . '_firewall';
    }


    /**
     * Return listener ID.
     *
     * @return string
     */
    protected function getListenerId()
    {
        return $this->getServicePrefix() . '.firewall.listener';
    }

    /**
     * Create AuthProvider.
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container      Symfony DI
     * @param string                                                  $id             Firewall id
     * @param mixed                                                   $config         Configuration
     * @param string                                                  $userProviderId User provider service ID
     *
     * @return string
     */
    protected function createAuthProvider(ContainerBuilder $container, $id, $config, $userProviderId)
    {
        if (isset($config['provider'])) {
            $authProviderId = $this->getServicePrefix() . '.auth.provider.'.$id;


            $container
                ->setDefinition($authProviderId, new DefinitionDecorator( $this->getServicePrefix() . '.auth.provider'))
                ->addArgument(new Reference( $this->getServicePrefix() . '.oauth.proxy'))
                ->addArgument(new Reference( $this->getServicePrefix() . '.user.provider'))
                ->addArgument(new Reference('service_container'))
            ;

            return $authProviderId;
        }

        // without user provider
        return $this->getServicePrefix() . '.auth.provider';
    }

    /**
     * Return position.
     *
     * @return string
     */
    public function getPosition()
    {
        return 'pre_auth';
    }

    /**
     * Implement this to return service prefix. Example: return 'facebook'
     * @return string
     */
    abstract function getServicePrefix();
}
