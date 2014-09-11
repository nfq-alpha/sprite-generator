<?php

namespace SpriteGenerator\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        // TODO: how to set default values so that we wouldn't have to do it in all configs

        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('sprite_generator');

        $rootNode
            ->children()
                ->arrayNode('sprites')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('inDir')->end()
                            ->scalarNode('outImage')->end()
                            ->scalarNode('outCss')->end()
                            ->scalarNode('relativeImagePath')->end()
                            ->scalarNode('padding')->end()
                            ->scalarNode('spriteClass')->end()
                            ->scalarNode('cssFormat')
                                ->defaultValue('sass')
                            ->end()
                            ->scalarNode('imagePositioning')
                                ->defaultValue('one-column')
                            ->end()
                            ->scalarNode('imageGenerator')
                                ->defaultValue('gd2')
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
