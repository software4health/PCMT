<?php

declare(strict_types=1);

namespace Pcmt\PcmtFamilyBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PcmtFamilyExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('repositories.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('jobs.yml');
        $loader->load('job_defaults.yml');
        $loader->load('job_constraints.yml');
        $loader->load('queries.yml');
        $loader->load('updaters.yml');
    }

    public function getAlias(): string
    {
        return 'pcmt_family';
    }
}