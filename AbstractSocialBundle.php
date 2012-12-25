<?php

namespace Ailove\AbstractSocialBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Ailove\AbstractSocialBundle\DependencyInjection\SocialCompilerPass;

class AbstractSocialBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new SocialCompilerPass());
    }

}
