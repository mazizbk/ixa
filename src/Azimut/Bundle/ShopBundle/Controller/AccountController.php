<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-10-04 09:25:13
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AccountController extends AbstractShopFrontController
{
    public function loginAction(Request $request, SessionInterface $session)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        // Redirect if basket is empty
        $basket = $this->get('azimut_shop.basket')->getBasket();
        if (0 == $basket->getOrderItems()->count()) {
            return $this->redirectToRoute('azimut_shop_basket_index');
        }

        $site = $this->getSite($request);

        return $this->forward('AzimutFrontofficeSecurityBundle:Login:login', [
            'template' => 'SiteLayout/'.($site->getLayout()->getShopLoginTemplate()?:'shop_login.html.twig'),
            'loginPath' => 'azimut_shop_account_login',
            'targetUrl' => $this->generateUrl('azimut_shop_order_addresses'),
            'targetFailUrl' => $this->generateUrl($request->get('_route')),
            '_route' => $request->attributes->get('_route'),
            '_route_params' => $request->attributes->get('_route_params'),
        ]);
    }

    public function registerAction(Request $request, SessionInterface $session)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        // Redirect if basket is empty
        $basket = $this->get('azimut_shop.basket')->getBasket();
        if (0 == $basket->getOrderItems()->count()) {
            return $this->redirectToRoute('azimut_shop_basket_index');
        }

        $site = $this->getSite($request);

        // If user already logged in, redirect to next step
        if ($this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute('azimut_shop_order_addresses');
        }

        return $this->forward('AzimutFrontofficeSecurityBundle:Login:register', [
            'template' => 'SiteLayout/'.($site->getLayout()->getShopRegisterTemplate()?:'shop_register.html.twig'),
            'registerPath' => 'azimut_shop_account_register',
            '_route' => $request->attributes->get('_route'),
            '_route_params' => $request->attributes->get('_route_params'),
        ]);
    }
}
