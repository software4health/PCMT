<?php

declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PcmtProductExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('savers.yml');
        $loader->load('updaters.yml');
        $loader->load('normalizers.yml');
        $loader->load('controllers.yml');
        $loader->load('fetchers.yml');
        $loader->load('widget.yml');
        $loader->load('services.yml');
        $loader->load('writers.yml');
        $loader->load('steps.yml');
        $loader->load('jobs.yml');
        $loader->load('forms.yml');
        $loader->load('providers.yml');
        $loader->load('event_subscribers.yml');
        $loader->load('entities.yml');
    }

    public function getAlias(): string
    {
        return 'pcmt_product';
    }
}