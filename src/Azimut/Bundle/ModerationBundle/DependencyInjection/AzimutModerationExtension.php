<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 11:50:28
 */

namespace Azimut\Bundle\ModerationBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class AzimutModerationExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }


    public function prepend(ContainerBuilder $container)
    {
        // add js and css files to backoffice assets
        if (in_array('moderation', $container->getParameter('active_backoffice_apps'))) {
            $config = [
                'backoffice_js_files' => [
                    'inputs' => [
                        '@AzimutModerationBundle/Resources/angularjs/ModerationApp.js',
                        '@AzimutModerationBundle/Resources/angularjs/Controller/*.js',
                        '@AzimutModerationBundle/Resources/angularjs/Directive/*.js',
                        '@AzimutModerationBundle/Resources/angularjs/Service/*.js',
                        '@AzimutModerationBundle/Resources/angularjs/Filter/*.js'
                    ]
                ],
                'backoffice_css_files' => [
                    'inputs' => [
                        '@AzimutModerationBundle/Resources/less/backoffice.less'
                    ]
                ]
            ];

            $container->prependExtensionConfig('assetic', ['assets' => $config]);
        }
    }
}
