<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-14 14:36:16
 */

namespace Azimut\Bundle\ShopBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Azimut\Bundle\ShopBundle\DependencyInjection\Compiler\DeliveryProviderPass;
use Azimut\Bundle\ShopBundle\DependencyInjection\Compiler\PaymentProviderPass;

class AzimutShopBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container
            ->addCompilerPass(new DeliveryProviderPass())
            ->addCompilerPass(new PaymentProviderPass())
        ;
    }
}
