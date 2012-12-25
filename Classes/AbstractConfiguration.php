<?php


namespace Ailove\AbstractSocialBundle\Classes;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\NodeDefinition;

abstract class AbstractConfiguration implements ConfigurationInterface
{

    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root($this->getTreeName());

        $this->buildTree($rootNode);

        return $treeBuilder;


    }

    /**
     * @param ArrayNodeDefinition|NodeDefinition $rootNode
     */
    public function buildTree($rootNode)
    {
        $rootNode
            ->children()
                //application data for pro server
                ->scalarNode('app_id')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;
        $rootNode
            ->children()
                ->scalarNode('access_token_url')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;
        $rootNode
            ->children()
                ->scalarNode('dialog_url')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;
        $rootNode
            ->children()
                ->scalarNode('app_secret')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;
        $rootNode
            ->children()
                ->scalarNode('app_public_key')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;
        $rootNode
            ->children()
                ->scalarNode('redirect_route')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;
        $rootNode
            ->children()
                ->scalarNode('scope')
                    ->cannotBeEmpty()
                ->end()
            ->end()
        ;
        $rootNode
            ->children()
                ->arrayNode('developer_apps')
                    ->useAttributeAsKey('id')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('app_id')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            ->scalarNode('app_secret')
                                ->isRequired()
                                ->cannotBeEmpty()
                            ->end()
                            //for Odnoklassniki optional
                            ->scalarNode('app_public_key')
                                ->cannotBeEmpty()
                            ->end()
                ->end()
            ->end()
        ;


        $rootNode
            ->children()
                ->arrayNode('class')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('api')->defaultNull()->end()
                        ->scalarNode('proxy')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;

    }

    /**
     * @return string id of the root tree
     */
    abstract protected function getTreeName();

}
