<?php

namespace FieldInteractive\CitoBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('cito');

        $rootNode
            ->children()
            ->scalarNode('pages')->defaultValue('%kernel.project_dir%/pages/')->end()
            ->scalarNode('posts')->defaultValue('%kernel.project_dir%/public/posts/')->end()
            ->booleanNode('user_agent_enabled')->defaultValue(false)->end()
            ->scalarNode('default_user_agent')->defaultValue('')->end()
            ->arrayNode('user_agent_routing')->scalarPrototype()->end()->end()
            ->end();

        return $treeBuilder;
    }
}
