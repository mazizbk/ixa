<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-27 10:31:46
 */

namespace Azimut\Bundle\ShopBundle\Event;

use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\ShopBundle\Entity\OrderItem;

class BasketBeforeItemDeletedEvent extends AbstractBasketEvent
{
    const NAME = 'shop.basket.before.item.deleted';

    /**
     * @var OrderItem
     */
    protected $orderItem;

    public function __construct(Order $basket, OrderItem $orderItem)
    {
        parent::__construct($basket);
        $this->orderItem = $orderItem;
    }

    /**
     * @return OrderItem
     */
    public function getOrderItem()
    {
        return $this->orderItem;
    }
}
