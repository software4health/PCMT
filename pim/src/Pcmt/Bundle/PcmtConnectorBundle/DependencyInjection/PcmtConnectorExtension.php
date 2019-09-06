<?php
declare(strict_types=1);

namespace Pcmt\Bundle\PcmtConnectorBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class PcmtConnectorExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
       $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
       $loader->load('factories.yml');
       $loader->load('handlers.yml');
       $loader->load('jobs.yml');
       $loader->load('steps.yml');
       $loader->load('savers.yml');
       $loader->load('readers.yml');
       $loader->load('writers.yml');
       $loader->load('job_constraints.yml');
       $loader->load('job_defaults.yml');
       $loader->load('services.yml');
       $loader->load('processors.yml');
       $loader->load('utils.yml');
    }
}