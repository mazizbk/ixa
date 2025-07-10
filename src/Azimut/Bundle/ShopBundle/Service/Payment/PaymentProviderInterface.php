<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-11 09:57:23
 */

namespace Azimut\Bundle\ShopBundle\Service\Payment;

use Azimut\Bundle\ShopBundle\Entity\Order;

interface PaymentProviderInterface
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
     * Return a route name to handle action
     *
     * @return string
     */
    public function getRoute();

    /**
     * Is payment deferred in time (like a check payment)
     *
     * @return boolean
     */
    public function isDeferred();
}
