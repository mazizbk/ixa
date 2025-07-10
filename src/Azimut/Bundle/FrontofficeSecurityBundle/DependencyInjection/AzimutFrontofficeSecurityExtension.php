<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-04 09:24:18
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class AzimutFrontofficeSecurityExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter(
            'azimut_frontoffice_security.frontoffice_user_roles',
            $config['frontoffice_user_roles']
        );
    }

    public function prepend(ContainerBuilder $container)
    {
        // add js and css files to backoffice assets
        if (in_array('frontoffice_security', $container->getParameter('active_backoffice_apps'))) {
            $config = [
                'backoffice_js_files' => [
                    'inputs' => [
                        '@AzimutFrontofficeSecurityBundle/Resources/angularjs/FrontofficeSecurityApp.js',
                        '@AzimutFrontofficeSecurityBundle/Resources/angularjs/Controller/*.js',
                        '@AzimutFrontofficeSecurityBundle/Resources/angularjs/Directive/*.js',
                        '@AzimutFrontofficeSecurityBundle/Resources/angularjs/Service/*.js',
                        '@AzimutFrontofficeSecurityBundle/Resources/angularjs/Filter/*.js'
                    ]
                ],
                'backoffice_css_files' => [
                    'inputs' => [
                        '@AzimutFrontofficeSecurityBundle/Resources/less/backoffice.less'
                    ]
                ]
            ];

            $container->prependExtensionConfig('assetic', ['assets' => $config]);
        }
    }
}
