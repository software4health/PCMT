<?php

namespace Pcmt\PcmtAttributeBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;


class PcmtAttributeExtension extends Extension {

  public function load(array $configs, ContainerBuilder $container)
  {
    $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
    $loader->load('array_converters.yml');
    $loader->load('entities.yml');
    $loader->load('normalizers.yml');
    $loader->load('updaters.yml');
  }
}