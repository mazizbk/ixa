<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-10-30 10:26:35
 */

namespace Azimut\Bundle\CmsContactBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class AzimutCmsContactExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        // add js and css files to backoffice assets
        if (in_array('cms_contact', $container->getParameter('active_backoffice_apps'))) {
            $config = [
                'backoffice_js_files' => [
                    'inputs' => [
                        '@AzimutCmsContactBundle/Resources/angularjs/CmsContactApp.js',
                        '@AzimutCmsContactBundle/Resources/angularjs/Controller/*.js',
                        '@AzimutCmsContactBundle/Resources/angularjs/Directive/*.js',
                        '@AzimutCmsContactBundle/Resources/angularjs/Service/*.js',
                        '@AzimutCmsContactBundle/Resources/angularjs/Filter/*.js'
                    ]
                ],
                'backoffice_css_files' => [
                    'inputs' => [
                        '@AzimutCmsContactBundle/Resources/less/backoffice.less'
                    ]
                ]
            ];

            $container->prependExtensionConfig('assetic', ['assets' => $config]);
        }
    }
}
