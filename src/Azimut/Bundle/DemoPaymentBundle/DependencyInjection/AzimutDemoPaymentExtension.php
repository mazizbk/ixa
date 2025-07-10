<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-13 11:10:08
 */

namespace Azimut\Bundle\DemoPaymentBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

class AzimutDemoPaymentExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));

        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader->load('services.yml');

        $container->setParameter('azimut_demo_payment.normal_return_url', $config['normal_return_url']);
        $container->setParameter('azimut_demo_payment.cancel_return_url', $config['cancel_return_url']);
        $container->setParameter('azimut_demo_payment.automatic_response_url', $config['automatic_response_url']);
    }
}
