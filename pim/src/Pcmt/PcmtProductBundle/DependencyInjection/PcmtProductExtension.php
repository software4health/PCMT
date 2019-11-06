<?php
declare(strict_types=1);

namespace Pcmt\PcmtProductBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PcmtProductExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('savers.yml');
        $loader->load('normalizers.yml');
        $loader->load('controllers.yml');
        $loader->load('fetchers.yml');
        $loader->load('widget.yml');
        $loader->load('services.yml');
    }

    public function getAlias(): string
    {
        return 'pcmt_product';
    }
}