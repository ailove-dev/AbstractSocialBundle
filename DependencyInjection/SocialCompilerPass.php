<?php

namespace Ailove\AbstractSocialBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class SocialCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        $socialEntryPointId = 'social.security.authentication.entry_point';

        if (!$container->hasDefinition($socialEntryPointId)) {
            return;
        }

        $definition = $container->getDefinition($socialEntryPointId);

        $taggedServices = $container->findTaggedServiceIds(
            'social.entryPoint'
        );
        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addEntryPoint',
                array(new Reference($id))
            );
        }
    }
}

