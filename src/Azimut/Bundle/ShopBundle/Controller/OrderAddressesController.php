<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-10-04 15:32:06
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Azimut\Bundle\ShopBundle\Entity\OrderAddress;
use Azimut\Bundle\ShopBundle\Form\Type\OrderAddressesType;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUserAddress;

class OrderAddressesController extends AbstractShopFrontController
{
    public function chooseAddressesAction(Request $request)
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
        $user = $this->getUser();
        $basket = $this->get('azimut_shop.basket')->getBasket();
        $basket->setUser($user);

        // Redirect if basket is empty
        $basket = $this->get('azimut_shop.basket')->getBasket();
        if (0 == $basket->getOrderItems()->count()) {
            return $this->redirectToRoute('azimut_shop_basket_index');
        }


        // Prefill order adresses from user addresses
        if (null == $basket->getBillingAddress()) {
            $userBillingAddress = $user->getAddress();
            $billingAddress = null;
            if (null != $userBillingAddress) {
                $billingAddress = OrderAddress::createFromUserAddress($user, $userBillingAddress);
            }
            $basket->setBillingAddress($billingAddress);
        }
        if (null == $basket->getDeliveryAddress()) {
            $userDeliveryAddress = $user->getDeliveryAddress();
            $deliveryAddress = null;

            if (null != $userDeliveryAddress) {
                $deliveryAddress = OrderAddress::createFromUserAddress($user, $userDeliveryAddress);
            }
            elseif (null != $userBillingAddress) {
                $deliveryAddress = OrderAddress::createFromUserAddress($user, $userBillingAddress);
            }
            $basket->setDeliveryAddress($deliveryAddress);
        }

        $form = $this->createForm(OrderAddressesType::class, $basket);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Store order addresses in user (if null in user object)
            if (null === $user->getAddress()) {
                $user->setAddress(FrontofficeUserAddress::createFromBaseAddress($basket->getBillingAddress()));
            }
            if (null === $user->getDeliveryAddress()) {
                $user->setDeliveryAddress(FrontofficeUserAddress::createFromBaseAddress($basket->getDeliveryAddress()));
            }

            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('azimut_shop_delivery');
        }

        return $this->render('SiteLayout/'.($site->getLayout()->getShopOrderAddressesTemplate()?:'shop_order_addresses.html.twig'), [
            'siteLayout'      => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle'       => $this->get('translator')->trans('addresses'),
            'pageDescription' => '',
            'site'            => $site,
            'form'            => $form->createView(),
        ]);
    }
}
