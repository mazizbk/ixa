<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-06-25
 */

namespace Azimut\Bundle\CmsBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

class AzimutCmsExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('azimut_cms.config', $config);

        $container->setParameter(
            'azimut_cms.comment_ratings',
            $config['comment_ratings']
        );
    }

    public function prepend(ContainerBuilder $container)
    {
        // add js and css files to backoffice assets
        if (in_array('cms', $container->getParameter('active_backoffice_apps'))) {
            $config = [
                'backoffice_js_files' => [
                    'inputs' => [
                        '@AzimutCmsBundle/Resources/angularjs/CmsApp.js',
                        '@AzimutCmsBundle/Resources/angularjs/Controller/*.js',
                        '@AzimutCmsBundle/Resources/angularjs/Directive/*.js',
                        '@AzimutCmsBundle/Resources/angularjs/Service/*.js',
                        '@AzimutCmsBundle/Resources/angularjs/Filter/*.js'
                    ]
                ],
                'backoffice_css_files' => [
                    'inputs' => [
                        '@AzimutCmsBundle/Resources/less/backoffice.less'
                    ]
                ]
            ];

            $container->prependExtensionConfig('assetic', ['assets' => $config]);
        }
    }
}
