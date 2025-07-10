<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-11 10:49:45
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Azimut\Bundle\ShopBundle\Form\Type\PaymentType;
use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\ShopBundle\Service\OrderStatusProvider;

class PaymentController extends AbstractShopFrontController
{
    public function choosePaymentAction(Request $request, $isEmbed = false)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        // Redirect anonymous users
        if ($anonymousUserRedirection = $this->getAnonymousUserRedirection($request)) {
            return $anonymousUserRedirection;
        }

        $basket = $this->get('azimut_shop.basket')->getBasket();

        // Redirect if delivery have not been choosen
        $basket = $this->get('azimut_shop.basket')->getBasket();
        if (null == $basket->getDeliveryProviderId()) {
            return $this->redirectToRoute('azimut_shop_delivery');
        }

        $site = $this->getSite($request);

        $form = $this->createForm(PaymentType::class, null, [
            'order' => $basket,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $paymentProviderId = $form->getData()['payment'];
            $paymentProvider = $this->get($paymentProviderId);

            $basket->setPaymentProvider($paymentProvider);
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute($paymentProvider->getRoute(), ['orderNumber' => $basket->getNumber()]);
        }

        if (true === $isEmbed) {
            $template = 'SiteLayout/'.($site->getLayout()->getShopPaymentEmbedTemplate()?:'shop_payment_form.html.twig');
        }
        else {
            $template = 'SiteLayout/'.($site->getLayout()->getShopPaymentTemplate()?:'shop_payment.html.twig');
        }

        return $this->render($template, [
            'siteLayout'      => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle'       => $this->get('translator')->trans('payment'),
            'pageDescription' => '',
            'site'            => $site,
            'form'            => $form->createView(),
        ]);
    }

    public function paymentConfirmedAction(Request $request, $orderNumber)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        // Redirect anonymous users
        if ($anonymousUserRedirection = $this->getAnonymousUserRedirection($request)) {
            return $anonymousUserRedirection;
        }

        $em = $this->getDoctrine()->getManager();

        $this->get('azimut_shop.basket')->closeBasket();

        $site = $this->getSite($request);
        $order = $em->getRepository(Order::class)->findOneBy([
            'number' => $orderNumber,
            'user'   => $this->get('security.token_storage')->getToken()->getUser(),
            // We don't filter on PAID status because this is just an information, we won't set the status here
        ]);

        if (null == $order) {
            throw $this->createNotFoundException(sprintf('Unable to find paid order number %s for this user', $orderNumber));
        }

        return $this->render('SiteLayout/shop_payment_confirmed.html.twig', [
            'siteLayout'        => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle'         => $this->get('translator')->trans('payment.confirmed'),
            'pageDescription'   => '',
            'site'              => $site,
            'order'             => $order,
        ]);
    }

    public function paymentRefusedAction(Request $request, $orderNumber)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        // Redirect anonymous users
        if ($anonymousUserRedirection = $this->getAnonymousUserRedirection($request)) {
            return $anonymousUserRedirection;
        }

        $site = $this->getSite($request);

        return $this->render('SiteLayout/shop_payment_refused.html.twig', [
            'siteLayout'        => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle'         => $this->get('translator')->trans('payment.refused'),
            'pageDescription'   => '',
            'site'              => $site,
        ]);
    }
}
