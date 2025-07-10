<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-11 10:09:46
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Azimut\Bundle\ShopBundle\Entity\Order;

class PaymentCheckController extends AbstractShopFrontController
{
    public function indexAction(Request $request, $orderNumber)
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
            'user' => $this->get('security.token_storage')->getToken()->getUser(),
        ]);

        return $this->render('SiteLayout/shop_payment_check.html.twig', [
            'siteLayout'        => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle'         => $this->get('translator')->trans('check.payment'),
            'pageDescription'   => '',
            'site'              => $site,
            'order'              => $order,
            'checkPayableTo'    => $this->container->getParameter('shop_check_payment_payable_to'),
            'addressName'       => $this->container->getParameter('shop_check_payment_address_name'),
            'addressLine1'      => $this->container->getParameter('shop_check_payment_address_line1'),
            'addressLine2'      => $this->container->getParameter('shop_check_payment_address_line2'),
            'addressPostalCode' => $this->container->getParameter('shop_check_payment_address_postal_code'),
            'addressCity'       => $this->container->getParameter('shop_check_payment_address_city'),
        ]);
    }
}
