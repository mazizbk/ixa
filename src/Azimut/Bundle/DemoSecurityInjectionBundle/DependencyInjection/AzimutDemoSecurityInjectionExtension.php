<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-21 09:57:39
 */

namespace Azimut\Bundle\DemoSecurityInjectionBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class AzimutDemoSecurityInjectionExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.yml');
    }
}
