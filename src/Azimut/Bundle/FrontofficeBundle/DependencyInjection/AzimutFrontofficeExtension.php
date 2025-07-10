<?php

namespace Azimut\Bundle\FrontofficeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class AzimutFrontofficeExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader->load('services.yml');

        $container->setParameter(
            'azimut_frontoffice.search_engine',
            $config['search_engine']
        );
    }

    public function prepend(ContainerBuilder $container)
    {
        // add js and css files to backoffice assets
        if (in_array('frontoffice', $container->getParameter('active_backoffice_apps'))) {
            $config = [
                'backoffice_js_files' => [
                    'inputs' => [
                        '@AzimutFrontofficeBundle/Resources/angularjs/FrontofficeApp.js',
                        '@AzimutFrontofficeBundle/Resources/angularjs/Controller/*.js',
                        '@AzimutFrontofficeBundle/Resources/angularjs/Directive/*.js',
                        '@AzimutFrontofficeBundle/Resources/angularjs/Service/*.js',
                        '@AzimutFrontofficeBundle/Resources/angularjs/Filter/*.js'
                    ]
                ],
                'backoffice_css_files' => [
                    'inputs' => [
                        '@AzimutFrontofficeBundle/Resources/less/backoffice.less'
                    ]
                ]
            ];

            $container->prependExtensionConfig('assetic', ['assets' => $config]);
        }
    }
}
