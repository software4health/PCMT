<?php

declare(strict_types=1);

namespace Pcmt\PcmtConnectorBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): void
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pcmt_job_connector');

        //set the dirpath in the DefaultJobProvider to be configurable:
        $rootNode
            ->children()
            ->arrayNode('data_download')
            ->children()
            ->scalarNode('dirPath')->end(); //implement default value
    }
}