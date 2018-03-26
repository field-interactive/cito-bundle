<?php
/**
 * Created by PhpStorm.
 * User: timkompernass
 * Date: 26.03.2018
 * Time: 11:41
 */

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
            ->end()
        ;

        return $treeBuilder;
    }
}
