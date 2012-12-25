<?php


namespace Ailove\AbstractSocialBundle\Classes;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

abstract class AbstractExtension extends Extension
{


    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->newConfiguration();
        $config = $this->processConfiguration($configuration, $configs);

        foreach ($config as $name => $val) {
            if (!is_array($val) && !empty($val)) {
                $container->setParameter($this->getParametersPrefix() . $name, $val);
            }
        }

//        var_dump($config);die;

        if (!empty($config['class']['api']) ) {
            $container->setParameter($this->getParametersPrefix() . 'class.api' , $config['class']['api']);
        }

        if (!empty($config['class']['proxy']) ) {
            $container->setParameter($this->getParametersPrefix() . 'class.proxy' , $config['class']['api']);
        }

        if (isset($config['developer_apps']) && is_array($config['developer_apps']) && sizeof($config['developer_apps'])) {
            foreach ($config['developer_apps'] as $host => $conf) {
                foreach ($conf as $key => $val) {
                    $container->setParameter($this->getParametersPrefix() . $host . '.' . $key, $val);
                }
            }
        }

    }

    /**
     * @return string prefix for bundle parameters
     */
    abstract protected function getParametersPrefix();

    /**
     * we should be able to create configuration from different non-abstract namespace
     * @return AbstractConfiguration
     */
    abstract protected function newConfiguration();

}
