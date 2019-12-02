<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pcmt_product');

        $rootNode
            ->children()
            ->integerNode('defaultSessionTimeMins')->defaultValue(20)->end()
            ->end()
            ->children()
            ->arrayNode('data_download')
            ->children()
            ->scalarNode('dirPath')->end() //implement default value
            ->end();

        return $treeBuilder;
    }
}