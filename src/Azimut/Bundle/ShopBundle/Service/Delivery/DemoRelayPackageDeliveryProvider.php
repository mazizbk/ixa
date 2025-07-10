<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-11-08 14:18:00
 */

namespace Azimut\Bundle\ShopBundle\Service\Delivery;

use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\ShopBundle\Entity\DeliveryTracking;

class DemoRelayPackageDeliveryProvider extends AbstractDeliveryProvider
{
    protected $intermediateRoute = 'azimut_shop_delivery_demo_relay_package';

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->translator->trans('demo.relay.package.delivery');
    }

    /**
     * {@inheritdoc}
     */
    public function getImage()
    {
        return 'bundles/azimutshop/img/demo-relay-package-delivery.png';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->translator->trans('Duis aute irure dolor in reprehenderit in voluptate velit esse
cillum dolore eu fugiat nulla pariatur.');
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailableForOrder(Order $order)
    {
        if ($order->getTotalAmount() > 100) {
            return true;
        }
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingCost(Order $order)
    {
        return 650;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDeliveryTracking()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function updateDeliveryTracking(DeliveryTracking $deliveryTracking)
    {
        // Here we should call the actual supplier API to retrieve up to date informations about delivery
        // ......($deliveryTracking->getCode());

        // After data fetched from API (for demo and debug purpose, don't use this as is) :
        // $deliveryTracking
        //     ->setLabel('Lorem ipsum dolor')
        //     ->setShippingDate(new \DateTime())
        //     ->setDeliveryDate(new \DateTime())
        //     ->isDelivered(true)
        // ;
        //
        // if (true) {
        //     // Set the delivery status based on specific supplier API information
        //     $deliveryTracking->isDelivered(true);
        // }
        //
        // $this->entityManager->flush($deliveryTracking);

        return $this;
    }
}
