<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-09 14:45:13
 */

namespace Azimut\Bundle\DemoExternalAppBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class AzimutDemoExternalAppExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        // add js and css files to backoffice assets
        if (in_array('demo_external_app', $container->getParameter('active_backoffice_apps'))) {
            $config = [
                'backoffice_js_files' => [
                    'inputs' => [
                        '@AzimutDemoExternalAppBundle/Resources/angularjs/DemoExternalAppApp.js',
                    ]
                ]
            ];

            $container->prependExtensionConfig('assetic', ['assets' => $config]);
        }
    }
}
