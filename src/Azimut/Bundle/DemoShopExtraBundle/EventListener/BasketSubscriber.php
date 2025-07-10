<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-27 10:47:19
 */

namespace Azimut\Bundle\DemoShopExtraBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Azimut\Bundle\ShopBundle\Event\AbstractBasketEvent;
use Azimut\Bundle\ShopBundle\Event\BasketBeforeItemAddedEvent;
use Azimut\Bundle\ShopBundle\Event\BasketAfterItemAddedEvent;
use Azimut\Bundle\ShopBundle\Event\BasketBeforeItemQuantityChangedEvent;
use Azimut\Bundle\ShopBundle\Event\BasketAfterItemQuantityChangedEvent;
use Azimut\Bundle\ShopBundle\Event\BasketBeforeItemDeletedEvent;
use Azimut\Bundle\ShopBundle\Event\BasketAfterItemDeletedEvent;
use Symfony\Component\Translation\TranslatorInterface;
use Azimut\Bundle\ShopBundle\Entity\OrderItem;
use Doctrine\ORM\EntityManagerInterface;

class BasketSubscriber implements EventSubscriberInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager)
    {
        $this->translator = $translator;
        $this->entityManager = $entityManager;
    }

    public static function getSubscribedEvents()
    {
        return [
            BasketBeforeItemAddedEvent::NAME => 'onBasketBeforeItemAddedEvent',
            BasketAfterItemAddedEvent::NAME => 'onBasketAfterItemAddedEvent',
            BasketBeforeItemQuantityChangedEvent::NAME => 'onBasketBeforeItemQuantityChangedEvent',
            BasketAfterItemQuantityChangedEvent::NAME => 'onBasketAfterItemQuantityChangedEvent',
            BasketBeforeItemDeletedEvent::NAME => 'onBasketBeforeItemDeletedEvent',
            BasketAfterItemDeletedEvent::NAME => 'onBasketAfterItemDeletedEvent',
        ];
    }

    public function onBasketBeforeItemAddedEvent(BasketBeforeItemAddedEvent $event)
    {
        $basket = $event->getBasket();
        $productItem = $event->getProductItem();

        if (5 <= $basket->count()) {
            $event->stopPropagation();
            $event->setPropagationStoppedMessage($this->translator->trans('basket.do.not.accept.more.than.5.products'));
        }
    }

    public function onBasketAfterItemAddedEvent(BasketAfterItemAddedEvent $event)
    {
        $basket = $event->getBasket();
        $orderItem = $event->getOrderItem();

        $this->handleDiscounts($event);
    }

    public function onBasketBeforeItemQuantityChangedEvent(BasketBeforeItemQuantityChangedEvent $event)
    {
        $basket = $event->getBasket();
        $orderItem = $event->getOrderItem();
    }

    public function onBasketAfterItemQuantityChangedEvent(BasketAfterItemQuantityChangedEvent $event)
    {
        $basket = $event->getBasket();
        $orderItem = $event->getOrderItem();

        $this->handleDiscounts($event);

        if (5 < $basket->count()) {
            $event->stopPropagation();
            $event->setPropagationStoppedMessage($this->translator->trans('basket.do.not.accept.more.than.5.products'));
        }
    }

    public function onBasketBeforeItemDeletedEvent(BasketBeforeItemDeletedEvent $event)
    {
        $basket = $event->getBasket();
        $orderItem = $event->getOrderItem();
    }

    public function onBasketAfterItemDeletedEvent(BasketAfterItemDeletedEvent $event)
    {
        $basket = $event->getBasket();
        $orderItem = $event->getOrderItem();

        $this->handleDiscounts($event);
    }

    private function handleDiscounts(AbstractBasketEvent $event)
    {
        $basket = $event->getBasket();

        // Add a 10€ discount when order amount greater than or equals 100€
        if ($basket->getTotalItemsAmount() >= 10000 && !$basket->hasDiscounts()) {
            $orderItem = new OrderItem();
            $orderItem
                ->setName('A demo discount')
                ->setPrice(-1000)
                ->isDeletable(false)
            ;
            $basket->addOrderItem($orderItem);
            $event->hasAddedOrDeletedOrderItems(true);
        }

        // Remove all discounts when order amount lower than 100€
        if ($basket->getTotalItemsAmount() < 10000 && $basket->hasDiscounts()) {
            foreach ($basket->getDiscounts() as $discount) {
                $this->entityManager->remove($discount);
            }
            $event->hasAddedOrDeletedOrderItems(true);
        }
    }
}
