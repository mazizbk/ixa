<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-13 09:40:14
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Azimut\Bundle\ShopBundle\Service\OrderStatusProvider;
use Azimut\Bundle\ShopBundle\Entity\PaymentProviderConfigurationDemo;
use Azimut\Bundle\ShopBundle\Entity\Order;

class PaymentDemoController extends AbstractShopFrontController
{
    public function checkoutAction(Request $request)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        // Redirect anonymous users
        if ($anonymousUserRedirection = $this->getAnonymousUserRedirection($request)) {
            return $anonymousUserRedirection;
        }

        $em = $this->getDoctrine()->getManager();
        $site = $this->getSite($request);
        $order = $this->get('azimut_shop.basket')->getBasket();

        // Generate whatever config object the payment system will need
        $paymentConfiguration = new PaymentProviderConfigurationDemo('EURO');
        $order->setPaymentProviderConfiguration($paymentConfiguration);

        // Example if we wanted to plug JMSPaymentCoreBundle
        // $instruction = new PaymentInstruction($amount, 978, 'sips');
        // $paymentConfiguration = new PaymentProviderConfigurationDemo($instruction);
        // $order->setPaymentProviderConfiguration($paymentConfiguration);

        $em->flush();

        // Call external payment system
        $paymentService = $this->get('azimut_demo_payment.demo_payment_service');
        $paymentServiceResponse = $paymentService->requestPayment([
            'amount' => $order->getTotalAmount(),
            'order_id' => $order->getNumber(),
        ]);

        return $this->render('SiteLayout/shop_payment_demo.html.twig', [
            'siteLayout'             => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle'              => $this->get('translator')->trans('demo.payment'),
            'pageDescription'        => '',
            'site'                   => $site,
            'order'                  => $order,
            'paymentServiceResponse' => $paymentServiceResponse,
        ]);
    }

    public function completeAction(Request $request)
    {
        // Here we should call the payment service for retrieving order from request
        // data, and check informations about the order (owned by current user),
        // because it's a demo, we don't
        // Ex :
        //  $this->get('azimut_demo_payment.demo_payment_service')->handleResponseData($request->request->get('DATA'))
        // ...

        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        // Redirect anonymous users
        if ($anonymousUserRedirection = $this->getAnonymousUserRedirection($request)) {
            return $anonymousUserRedirection;
        }

        return $this->redirectToRoute('azimut_shop_payment_confirmed', ['orderNumber' => $request->get('order_id')]);
    }

    public function cancelAction(Request $request)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        // Redirect anonymous users
        if ($anonymousUserRedirection = $this->getAnonymousUserRedirection($request)) {
            return $anonymousUserRedirection;
        }

        return $this->redirectToRoute('azimut_shop_payment_refused', ['orderNumber' => $request->get('order_id')]);
    }

    public function notificationAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $orderNumber = $request->get('order_id');
        $amount = $request->get('amount');
        $status = $request->get('status');

        // Here we should check informations ... but this is just a demo
        // ...

        $order = $em->getRepository(Order::class)->findOneByNumber($orderNumber);

        if (!$order) {
            throw $this->createNotFoundException('Unable to find order number '.$orderNumber);
        }

        if ('ACCEPTED' == $status) {
            $order
                ->setStatus(OrderStatusProvider::STATUS_PAID)
                ->setPaymentDate(new \DateTime()); // Replace new DateTime by real payment date from request data (this is a only a demo)
            ;
        }
        else {
            $order->setStatus(OrderStatusProvider::STATUS_PAIEMENT_REFUSED);
        }

        $em->flush();
        return new Response();
    }
}
