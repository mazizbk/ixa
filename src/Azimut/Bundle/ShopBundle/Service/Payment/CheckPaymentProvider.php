<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-11 10:05:44
 */

namespace Azimut\Bundle\ShopBundle\Service\Payment;

use Azimut\Bundle\ShopBundle\Entity\Order;

class CheckPaymentProvider extends AbstractPaymentProvider
{
    protected $route = 'azimut_shop_payment_check';

    protected $isDeferred = true;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->translator->trans('check.payment');
    }

    /**
     * {@inheritdoc}
     */
    public function getImage()
    {
        return 'bundles/azimutshop/img/check-payment.svg';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->translator->trans('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua');
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailableForOrder(Order $order)
    {
        return true;
    }
}
