<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-11-08 10:50:37
 */

namespace Azimut\Bundle\ShopBundle\Service\Delivery;

use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\ShopBundle\Entity\DeliveryTracking;

interface DeliveryProviderInterface
{
    /**
     * Get provider id (service name in container)
     *
     * @return string
     */
    public function getId();

    /**
     * Get provider id (service name in container)
     *
     * @return string
     */
    public function setId($id);

    /**
     * Get provider name (displayed to final user)
     *
     * @return string
     */
    public function getName();

    /**
     * Get provider image (displayed to final user)
     *
     * @return string
     */
    public function getImage();

    /**
     * Get provider description (displayed to final user)
     *
     * @return string
     */
    public function getDescription();

    /**
     * Return true if this delivery mode is available for an order
     *
     * @param  Order $order
     * @return boolean
     */
    public function isAvailableForOrder(Order $order);

    /**
     * Return the shipping cost for an order
     *
     * @param  Order  $order
     * @return int
     */
    public function getShippingCost(Order $order);

    /**
     * Return a route name to handle specific action before next step
     *
     * @return string|null
     */
    public function getIntermediateRoute();

    /**
     * Return true if provider has intermediate route
     *
     * @return bool
     */
    public function hasIntermediateRoute();

    /**
     * Return true if provider support delivery tracking
     *
     * @return bool
     */
    public function supportsDeliveryTracking();

    /**
     * Get updated delivery information from an external service and update delivery tracking object
     *
     * @return self
     */
    public function updateDeliveryTracking(DeliveryTracking $deliveryTracking);
}
