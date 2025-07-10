<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-13 09:40:25
 */

namespace Azimut\Bundle\ShopBundle\Service\Payment;

use Azimut\Bundle\ShopBundle\Entity\Order;

class DemoPaymentProvider extends AbstractPaymentProvider
{
    protected $route = 'azimut_shop_payment_demo';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->translator->trans('demo.payment');
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
        return $this->translator->trans('This is a demo payment provider reflecting a complete and independant payment system (see DemoPaymentBundle)');
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailableForOrder(Order $order)
    {
        return true;
    }
}
