<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-11 10:09:46
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Azimut\Bundle\ShopBundle\Form\Type\PaymentDemoSimpleType;
use Azimut\Bundle\ShopBundle\Service\OrderStatusProvider;
use Symfony\Component\Form\FormError;

class PaymentDemoSimpleController extends AbstractShopFrontController
{
    public function indexAction(Request $request)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        // Redirect anonymous users
        if ($anonymousUserRedirection = $this->getAnonymousUserRedirection($request)) {
            return $anonymousUserRedirection;
        }

        $site = $this->getSite($request);
        $order = $this->get('azimut_shop.basket')->getBasket();

        $form = $this->createForm(PaymentDemoSimpleType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $cardNumber = $form->getData()['cardNumber'];

            if (true === $this->get('azimut_shop.demo_payment_service')->requestPayment($order->getTotalAmount(), $cardNumber)) {
                $order->setStatus(OrderStatusProvider::STATUS_PAID);

                $em->flush();

                return $this->redirectToRoute('azimut_shop_payment_confirmed', ['orderNumber' => $order->getNumber()]);
            }
            else {
                $form->addError(new FormError($this->get('translator')->trans('payment.refused')));
            }
        }

        return $this->render('SiteLayout/shop_payment_demo_simple.html.twig', [
            'siteLayout'      => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle'       => $this->get('translator')->trans('demo.simple.payment'),
            'pageDescription' => '',
            'site'            => $site,
            'order'           => $order,
            'form'            => $form->createView(),
        ]);
    }
}
