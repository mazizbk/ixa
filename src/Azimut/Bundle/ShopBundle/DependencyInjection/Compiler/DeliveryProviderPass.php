<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-11-08 11:55:52
 */

namespace Azimut\Bundle\ShopBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class DeliveryProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('azimut_shop.delivery_provider_chain')) {
            return;
        }
        $definition = $container->findDefinition('azimut_shop.delivery_provider_chain');
        $taggedServices = $container->findTaggedServiceIds('azimut_shop.delivery_provider');
        $activeProviders = $container->getParameter('shop_active_delivery_provider');

        foreach ($taggedServices as $id => $tags) {
            if (in_array($id, $activeProviders)) {
                $definition->addMethodCall('addProvider', [ new Reference($id), $id ]);
            }
        }
    }
}
