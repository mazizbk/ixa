<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-11 16:11:19
 */

namespace Azimut\Bundle\ShopBundle\Service\Payment;

use Azimut\Bundle\ShopBundle\Entity\Order;

class DemoSimplePaymentProvider extends AbstractPaymentProvider
{
    protected $route = 'azimut_shop_payment_simple_demo';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->translator->trans('demo.simple.payment');
    }

    /**
     * {@inheritdoc}
     */
    public function getImage()
    {
        return 'bundles/azimutshop/img/demo-payment.svg';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->translator->trans('This is a simple demo payment provider that relies on an integrated and synchronous direct call to payment system');
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailableForOrder(Order $order)
    {
        return true;
    }
}
