<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-26 11:39:54
 */

namespace Azimut\Bundle\ShopBundle\Event;

class OrderPaidEvent extends AbstractOrderEvent
{
    const NAME = 'shop.order.payment.accepted';
}
