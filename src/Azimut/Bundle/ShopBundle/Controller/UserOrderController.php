<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-02-27 16:51:25
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Azimut\Bundle\ShopBundle\Entity\Order;

/**
 * @IsGranted("ROLE_FRONT_USER")
 */
class UserOrderController extends AbstractShopFrontController
{
    public function indexAction(Request $request)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        $site = $this->getSite($request);
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $orders = $this->getDoctrine()->getManager()->getRepository(Order::class)->findUserPlacedOrders($user);

        return $this->render('SiteLayout/'.($site->getLayout()->getShopUserOrdersTemplate()?:'user_orders.html.twig'), [
            'siteLayout'      => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle'       => $this->get('translator')->trans('your.orders'),
            'pageDescription' => '',
            'site'            => $site,
            'orders'          => $orders,
            'orderStatuses'   => $this->get('azimut_shop.order_status_provider')->getStatuses(),
        ]);
    }

    public function showAction(Request $request, $orderNumber)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        $site = $this->getSite($request);
        $user = $this->get('security.token_storage')->getToken()->getUser();

        $order = $this->getDoctrine()->getManager()->getRepository(Order::class)->findOneBy([
            'user' => $user,
            'number'   => $orderNumber,
        ]);

        if (null == $order) {
            return $this->createNotFoundException(sprintf('No order number "%s" found for user "%s"', $orderNumber, $user->getName()));
        }

        return $this->render('SiteLayout/'.($site->getLayout()->getShopUserOrderShowTemplate()?:'user_order_show.html.twig'), [
            'siteLayout'      => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle'       => $this->get('translator')->trans('your.orders'),
            'pageDescription' => '',
            'site'            => $site,
            'order'           => $order,
            'orderStatuses'   => $this->get('azimut_shop.order_status_provider')->getStatuses(),
        ]);
    }
}
