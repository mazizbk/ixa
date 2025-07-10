<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-11-18 14:15:47
 */

namespace Azimut\Bundle\FormExtraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class AzimutFormExtraExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        if (!isset($config['tinymce']['script_url'])) {
            throw new \InvalidArgumentException(
                'The "tinymce.script_url" option must be set for AzimutFormExtraBundle'
            );
        }

        if (!isset($config['tinymce']['content_css_url'])) {
            throw new \InvalidArgumentException(
                'The "tinymce.content_css_url" option must be set for AzimutFormExtraBundle'
            );
        }

        if (!isset($config['tinymce']['templates_url'])) {
            throw new \InvalidArgumentException(
                'The "tinymce.templates_url" option must be set for AzimutFormExtraBundle'
            );
        }

        $loader->load('services.yml');

        $container->setParameter(
            'azimut_form_extra.tinymce.script_url',
            $config['tinymce']['script_url']
        );

        $container->setParameter(
            'azimut_form_extra.tinymce.content_css_url',
            $config['tinymce']['content_css_url']
        );

        $container->setParameter(
            'azimut_form_extra.tinymce.templates_url',
            $config['tinymce']['templates_url']
        );
    }
}
