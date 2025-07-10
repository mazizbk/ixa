<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-22 12:05:33
 */

namespace Azimut\Bundle\CmsMapBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class AzimutCmsMapExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        // add js and css files to backoffice assets
        if (in_array('cms_map', $container->getParameter('active_backoffice_apps'))) {
            $config = [
                'backoffice_js_files' => [
                    'inputs' => [
                        '@AzimutCmsMapBundle/Resources/angularjs/CmsMapApp.js',
                        '@AzimutCmsMapBundle/Resources/angularjs/Controller/*.js',
                        '@AzimutCmsMapBundle/Resources/angularjs/Directive/*.js',
                        '@AzimutCmsMapBundle/Resources/angularjs/Service/*.js',
                        '@AzimutCmsMapBundle/Resources/angularjs/Filter/*.js'
                    ]
                ],
                'backoffice_css_files' => [
                    'inputs' => [
                        '@AzimutCmsMapBundle/Resources/less/backoffice.less'
                    ]
                ]
            ];

            $container->prependExtensionConfig('assetic', ['assets' => $config]);
        }
    }
}
