<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-11 10:38:30
 */

namespace Azimut\Bundle\ShopBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class PaymentProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('azimut_shop.payment_provider_chain')) {
            return;
        }
        $definition = $container->findDefinition('azimut_shop.payment_provider_chain');
        $taggedServices = $container->findTaggedServiceIds('azimut_shop.payment_provider');
        $activeProviders = $container->getParameter('shop_active_payment_provider');

        foreach ($taggedServices as $id => $tags) {
            if (in_array($id, $activeProviders)) {
                $definition->addMethodCall('addProvider', [ new Reference($id), $id ]);
            }
        }
    }
}
