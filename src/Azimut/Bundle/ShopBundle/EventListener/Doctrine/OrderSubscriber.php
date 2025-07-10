<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-11 13:43:31
 */

namespace Azimut\Bundle\ShopBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\ShopBundle\Event\OrderPlacedEvent;
use Azimut\Bundle\ShopBundle\Event\OrderPaidEvent;
use Azimut\Bundle\ShopBundle\Event\OrderPaymentRefusedEvent;
use Azimut\Bundle\ShopBundle\Event\OrderCancelledEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Azimut\Bundle\ShopBundle\Service\OrderStatusProvider;

class OrderSubscriber implements EventSubscriber
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var int
     */
    private $defaultVatRate;

    public function __construct(EventDispatcherInterface $eventDispatcher, $defaultVatRate)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->defaultVatRate = $defaultVatRate;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        if ($entity instanceof Order) {
            $this->handleOrderNumberAssignment($entity, $em);
            $this->handleTotalAmountStoring($entity);
            $this->dispatchOrderStatusEvents($entity);
        }
    }

    public function preUpdate(PreUpdateEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        if ($entity instanceof Order) {
            $this->handleOrderNumberAssignment($entity, $em);
            $this->handleTotalAmountStoring($entity);
            if ($eventArgs->hasChangedField('status')) {
                $this->dispatchOrderStatusEvents($entity);
            }
        }
    }

    /**
     * If a payment provider has been set on an order, we consider the order as placed, so
     * we assign an order number and fire the "order placed" event
     */
    private function handleOrderNumberAssignment(Order $order, $em)
    {
        if (null !== $order->getPaymentProviderId() && null === $order->getNumber()) {
            $lastTodayOrder = $em->getRepository(Order::class)->findTodayLastOrderHavingNumber();

            $todayOrderNumberIncrement = 0;
            if ($lastTodayOrder) {
                $todayOrderNumberIncrement = (int) substr($lastTodayOrder->getNumber(), -3);
            }

            $today = new \DateTime();

            // Format order number "year + month + day + 3 digits increment"
            $orderNumber = sprintf("%d%03d", $today->format('Ymd'), $todayOrderNumberIncrement + 1);

            $order
                ->setOrderDate($today)
                ->setNumber($orderNumber)
            ;
        }
    }

    /**
     * Store calculated total amounts on entity when status is "validated"
     */
    private function handleTotalAmountStoring(Order $order)
    {
        if (OrderStatusProvider::STATUS_VALIDATED == $order->getStatus()) {
            $order->setTotalAmount($order->getTotalAmount());
            $order->setTotalPretaxAmount($order->getTotalPretaxAmount($this->defaultVatRate));
        }
    }

    /**
     * Dispatch events based on new order status
     */
    private function dispatchOrderStatusEvents(Order $order)
    {
        if (OrderStatusProvider::STATUS_VALIDATED == $order->getStatus()) {
            $this->eventDispatcher->dispatch(OrderPlacedEvent::NAME, new OrderPlacedEvent($order));
        }

        if (OrderStatusProvider::STATUS_PAID == $order->getStatus()) {
            $this->eventDispatcher->dispatch(OrderPaidEvent::NAME, new OrderPaidEvent($order));
        }

        if (OrderStatusProvider::STATUS_PAIEMENT_REFUSED == $order->getStatus()) {
            $this->eventDispatcher->dispatch(OrderPaymentRefusedEvent::NAME, new OrderPaymentRefusedEvent($order));
        }

        if (OrderStatusProvider::STATUS_CANCELLED == $order->getStatus()) {
            $this->eventDispatcher->dispatch(OrderCancelledEvent::NAME, new OrderCancelledEvent($order));
        }
    }
}
