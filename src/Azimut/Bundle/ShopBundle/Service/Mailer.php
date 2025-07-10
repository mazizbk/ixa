<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-26 10:00:38
 */

namespace Azimut\Bundle\ShopBundle\Service;

use Azimut\Bundle\FrontofficeBundle\Service\AbstractRequestLocaleAwareMailer;
use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;

class Mailer extends AbstractRequestLocaleAwareMailer
{
    /**
     * Send a message to shop admin when order is confirmed but waiting for payment (ex: for check payment)
     */
    public function sendOrderPlacedAdminMail(Order $order)
    {
        TranslationProxy::setDefaultLocale($this->locale);
        $domain = $order->getSite()->getMainDomainName();

        $subject = $this->translator->trans('email.shop.new.order.at.%domain%', ['%domain%' => $domain]);

        $htmlBody = $this->templating->render(
            'Emails/Shop/admin_order_placed.html.twig',
            [
                'order'  => $order,
                'domain' => $domain,
            ]
        );

        $textBody = $this->templating->render(
            'Emails/Shop/admin_order_placed.txt.twig',
            [
                'order'  => $order,
                'domain' => $domain,
            ]
        );

        $this->sendMessage($this->adminRecipient, $subject, $htmlBody, $textBody);
    }

    /**
     * Send a message to user when order is confirmed but waiting for payment (ex: for check payment)
     */
    public function sendOrderPlacedUserMail(Order $order)
    {
        TranslationProxy::setDefaultLocale($order->getLocale());
        $domain = $order->getSite()->getMainDomainName();

        $subject = $this->translator->trans('email.shop.your.order.at.%domain%', ['%domain%' => $domain]);

        $htmlBody = $this->templating->render(
            'Emails/Shop/user_order_placed.html.twig',
            [
                'order'  => $order,
                'domain' => $domain,
            ]
        );

        $textBody = $this->templating->render(
            'Emails/Shop/user_order_placed.txt.twig',
            [
                'order'  => $order,
                'domain' => $domain,
            ]
        );

        $this->sendMessage($order->getUser()->getEmail(), $subject, $htmlBody, $textBody);
    }

    /**
     * Send a message to the user when order payment is refused
     */
    public function sendOrderPaymentRefusedUserMail(Order $order)
    {
        TranslationProxy::setDefaultLocale($order->getLocale());
        $domain = $order->getSite()->getMainDomainName();

        $subject = $this->translator->trans('email.shop.order.payment.refused.at.%domain%', ['%domain%' => $domain]);

        $htmlBody = $this->templating->render(
            'Emails/Shop/user_order_payment_refused.html.twig',
            [
                'order'  => $order,
                'domain' => $domain,
            ]
        );

        $textBody = $this->templating->render(
            'Emails/Shop/user_order_payment_refused.txt.twig',
            [
                'order'  => $order,
                'domain' => $domain,
            ]
        );

        $this->sendMessage($order->getUser()->getEmail(), $subject, $htmlBody, $textBody);
    }

    /**
     * Send a message to shop admin when order payment is refused
     */
    public function sendOrderCancelledAdminMail(Order $order)
    {
        TranslationProxy::setDefaultLocale($this->locale);
        $domain = $order->getSite()->getMainDomainName();

        $subject = $this->translator->trans('email.shop.order.cancellation.at.%domain%', ['%domain%' => $domain]);

        $htmlBody = $this->templating->render(
            'Emails/Shop/admin_order_cancelled.html.twig',
            [
                'order'  => $order,
                'domain' => $domain,
            ]
        );

        $textBody = $this->templating->render(
            'Emails/Shop/admin_order_cancelled.txt.twig',
            [
                'order'  => $order,
                'domain' => $domain,
            ]
        );

        $this->sendMessage($this->adminRecipient, $subject, $htmlBody, $textBody);
    }

    /**
     * Send a message to user when order payment is refused
     */
    public function sendOrderCancelledUserMail(Order $order)
    {
        TranslationProxy::setDefaultLocale($order->getLocale());
        $domain = $order->getSite()->getMainDomainName();

        $subject = $this->translator->trans('email.shop.order.cancellation.at.%domain%', ['%domain%' => $domain]);

        $htmlBody = $this->templating->render(
            'Emails/Shop/user_order_cancelled.html.twig',
            [
                'order'  => $order,
                'domain' => $domain,
            ]
        );

        $textBody = $this->templating->render(
            'Emails/Shop/user_order_cancelled.txt.twig',
            [
                'order'  => $order,
                'domain' => $domain,
            ]
        );

        $this->sendMessage($order->getUser()->getEmail(), $subject, $htmlBody, $textBody);
    }
}
