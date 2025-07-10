<?php

namespace Azimut\Behat\KernelExtension;

use Behat\Behat\Extension\ExtensionInterface;
use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

class Extension implements ExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function load(array $config, ContainerBuilder $container)
    {
        $container->setParameter('azimut.kernel_factory.app_dir', $config['app_dir']);

        $loader = new XmlFileLoader($container, new FileLocator(__DIR__ . '/config'));
        $loader->load('core.xml');
    }

    public function getConfig(ArrayNodeDefinition $builder)
    {
        $builder
            ->children()
                ->scalarNode('app_dir')->isRequired()->end()
            ->end()
        ;
    }

    public function getCompilerPasses()
    {
        return array();
    }
}
