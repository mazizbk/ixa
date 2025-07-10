<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-26 09:27:04
 */

namespace Azimut\Bundle\ShopBundle\Event;

class OrderPlacedEvent extends AbstractOrderEvent
{
    const NAME = 'shop.order.placed';
}
