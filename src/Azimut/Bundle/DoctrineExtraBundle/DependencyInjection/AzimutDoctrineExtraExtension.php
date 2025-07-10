<?php

namespace Azimut\Bundle\DoctrineExtraBundle\DependencyInjection;

use Azimut\Bundle\DoctrineExtraBundle\DataCollector\DoctrineDataCollector;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class AzimutDoctrineExtraExtension extends Extension
{
    public function load(array $config, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('doctrine.data_collector.class', DoctrineDataCollector::class);
    }
}
