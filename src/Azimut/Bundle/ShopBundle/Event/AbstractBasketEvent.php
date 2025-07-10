<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-27 10:29:28
 */

namespace Azimut\Bundle\ShopBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Azimut\Bundle\ShopBundle\Entity\Order;

class AbstractBasketEvent extends Event
{
    /**
     * @var Order
     */
    protected $basket;

    /**
     * @var boolean
     */
    protected $hasAddedOrDeletedOrderItems = false;

    /**
     * @var string
     */
    private $propagationStoppedMessage;

    public function __construct(Order $basket)
    {
        $this->basket = $basket;
    }

    /**
     * Get basket
     *
     * @return Order
     */
    public function getBasket()
    {
        return $this->basket;
    }

    /**
     * Get propagation stopped reason message
     *
     * @return string
     */
    public function getPropagationStoppedMessage()
    {
        return $this->propagationStoppedMessage;
    }

    /**
     * Set propagation stopped reason message
     *
     * @param string $propagationStoppedMessage
     *
     * @return self
     */
    public function setPropagationStoppedMessage($propagationStoppedMessage)
    {
        $this->propagationStoppedMessage = $propagationStoppedMessage;
        return $this;
    }

    /**
     * Get or set hasAddedOrDeletedOrderItems
     *
     * @param boolean|null
     *
     * @return boolean
     */
    public function hasAddedOrDeletedOrderItems($hasAddedOrDeletedOrderItems = null)
    {
        if (null !== $hasAddedOrDeletedOrderItems) {
            $this->hasAddedOrDeletedOrderItems = $hasAddedOrDeletedOrderItems;
            return $this;
        }

        return $this->hasAddedOrDeletedOrderItems;
    }
}
