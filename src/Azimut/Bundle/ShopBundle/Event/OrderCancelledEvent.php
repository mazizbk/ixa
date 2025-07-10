<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-26 12:22:51
 */

namespace Azimut\Bundle\ShopBundle\Event;

class OrderCancelledEvent extends AbstractOrderEvent
{
    const NAME = 'shop.order.cancelled';
}
