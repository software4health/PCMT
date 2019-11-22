<?php

declare(strict_types=1);

namespace Pcmt\Bundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pcmtservice');

        $rootNode
            ->children()
            ->booleanNode('pcmtActive')->defaultTrue()->end()
            ->integerNode('defaultSessionTimeMins')->defaultValue(20)->end()
            ->end();

        return $treeBuilder;
    }
}