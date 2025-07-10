<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-26 09:40:54
 */

namespace Azimut\Bundle\ShopBundle\EventListener;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Azimut\Bundle\ShopBundle\Service\Mailer;
use Azimut\Bundle\ShopBundle\Service\Payment\PaymentProviderChain;
use Azimut\Bundle\ShopBundle\Event\OrderPlacedEvent;
use Azimut\Bundle\ShopBundle\Event\OrderPaidEvent;
use Azimut\Bundle\ShopBundle\Event\OrderPaymentRefusedEvent;
use Azimut\Bundle\ShopBundle\Event\OrderCancelledEvent;

class OrderStatusSubscriber implements EventSubscriberInterface
{
    /**
     * @var Mailer
     */
    private $mailer;

    /**
     * @var PaymentProviderChain
     */
    private $paymentProviderChain;

    public function __construct(Mailer $mailer, PaymentProviderChain $paymentProviderChain)
    {
        $this->mailer = $mailer;
        $this->paymentProviderChain = $paymentProviderChain;
    }

    public static function getSubscribedEvents()
    {
        return [
            OrderPlacedEvent::NAME         => 'onOrderPlaced',
            OrderPaidEvent::NAME           => 'onOrderPaid',
            OrderPaymentRefusedEvent::NAME => 'onOrderPaymentRefused',
            OrderCancelledEvent::NAME      => 'onOrderCancelled',
        ];
    }

    public function onOrderPlaced(OrderPlacedEvent $event)
    {
        $order = $event->getOrder();
        if (null == $order->getPaymentProviderId()) { // Protection for edge cases, provider id should always be set
            return;
        }
        $paymentProvider = $this->paymentProviderChain->getProvider($order->getPaymentProviderId());

        // If payment provider is deferred, send confirmation email
        if ($paymentProvider->isDeferred()) {
            $this->mailer->sendOrderPlacedAdminMail($order);
            $this->mailer->sendOrderPlacedUserMail($order);
        }
    }

    public function onOrderPaid(OrderPaidEvent $event)
    {
        $order = $event->getOrder();
        if (null == $order->getPaymentProviderId()) { // Protection for edge cases, provider id should always be set
            return;
        }
        $paymentProvider = $this->paymentProviderChain->getProvider($order->getPaymentProviderId());

        // If payment provider is not deferred, send confirmation email
        if (!$paymentProvider->isDeferred()) {
            $this->mailer->sendOrderPlacedAdminMail($order);
            $this->mailer->sendOrderPlacedUserMail($order);
        }
    }

    public function onOrderPaymentRefused(OrderPaymentRefusedEvent $event)
    {
        $this->mailer->sendOrderPaymentRefusedUserMail($event->getOrder());
    }

    public function onOrderCancelled(OrderCancelledEvent $event)
    {
        $order = $event->getOrder();
        $this->mailer->sendOrderCancelledAdminMail($order);
        $this->mailer->sendOrderCancelledUserMail($order);
    }
}
