<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-26 11:40:56
 */

namespace Azimut\Bundle\ShopBundle\Event;

use Symfony\Component\EventDispatcher\Event;
use Azimut\Bundle\ShopBundle\Entity\Order;

class AbstractOrderEvent extends Event
{
    /**
     * @var Order
     */
    protected $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }
}
