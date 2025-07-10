<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-14 14:38:25
 */

namespace Azimut\Bundle\ShopBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class AzimutShopExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        // add js and css files to backoffice assets
        if (in_array('shop', $container->getParameter('active_backoffice_apps'))) {
            $config = [
                'backoffice_js_files' => [
                    'inputs' => [
                        '@AzimutShopBundle/Resources/angularjs/ShopApp.js',
                        '@AzimutShopBundle/Resources/angularjs/Controller/*.js',
                        '@AzimutShopBundle/Resources/angularjs/Directive/*.js',
                        '@AzimutShopBundle/Resources/angularjs/Service/*.js',
                        '@AzimutShopBundle/Resources/angularjs/Filter/*.js'
                    ]
                ],
                'backoffice_css_files' => [
                    'inputs' => [
                        '@AzimutShopBundle/Resources/less/backoffice.less'
                    ]
                ]
            ];

            $container->prependExtensionConfig('assetic', ['assets' => $config]);
        }
    }
}
