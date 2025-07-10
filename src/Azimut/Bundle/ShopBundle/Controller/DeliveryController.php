<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-11-08 11:42:09
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Azimut\Bundle\ShopBundle\Form\Type\DeliveryType;

class DeliveryController extends AbstractShopFrontController
{
    public function chooseDeliveryAction(Request $request)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        // Redirect anonymous users
        if ($anonymousUserRedirection = $this->getAnonymousUserRedirection($request)) {
            return $anonymousUserRedirection;
        }

        $site = $this->getSite($request);
        $this->get('azimut_shop.basket')->resetBasketStatus();
        $basket = $this->get('azimut_shop.basket')->getBasket();

        // Redirect if addresses are not set
        $basket = $this->get('azimut_shop.basket')->getBasket();
        if (null == $basket->getDeliveryAddress() || null == $basket->getBillingAddress()) {
            return $this->redirectToRoute('azimut_shop_order_addresses');
        }

        $form = $this->createForm(DeliveryType::class, null, [
            'order' => $basket,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $deliveryProviderId = $form->getData()['delivery'];
            $deliveryProvider = $this->get($deliveryProviderId);

            $basket->setDeliveryProvider($deliveryProvider);
            $this->getDoctrine()->getManager()->flush();

            if ($deliveryProvider->hasIntermediateRoute()) {
                return $this->redirectToRoute($deliveryProvider->getIntermediateRoute());
            }
            return $this->redirectToRoute('azimut_shop_summary');
        }

        return $this->render('SiteLayout/'.($site->getLayout()->getShopDeliveryTemplate()?:'shop_delivery.html.twig'), [
            'siteLayout'      => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle'       => $this->get('translator')->trans('delivery'),
            'pageDescription' => '',
            'site'            => $site,
            'form'            => $form->createView(),
        ]);
    }
}
