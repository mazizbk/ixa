<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-27 10:31:46
 */

namespace Azimut\Bundle\ShopBundle\Event;

use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\ShopBundle\Entity\BaseProductItem;

class BasketBeforeItemAddedEvent extends AbstractBasketEvent
{
    const NAME = 'shop.basket.before.item.added';

    /**
     * @var BaseProductItem
     */
    protected $productItem;

    public function __construct(Order $basket, BaseProductItem $productItem)
    {
        parent::__construct($basket);
        $this->productItem = $productItem;
    }

    /**
     * @return BaseProductItem
     */
    public function getProductItem()
    {
        return $this->productItem;
    }
}
