<?php

declare(strict_types=1);

namespace Pcmt\PcmtAttributeBundle\DependencyInjection\Configuration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): void
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('pcmt_attribute');
    }
}