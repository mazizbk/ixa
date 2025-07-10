<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-11-09 14:31:59
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

class SummaryController extends AbstractShopFrontController
{
    public function summaryAction(Request $request)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        // Redirect anonymous users
        if ($anonymousUserRedirection = $this->getAnonymousUserRedirection($request)) {
            return $anonymousUserRedirection;
        }

        // Redirect if delivery have not been choosen
        $basket = $this->get('azimut_shop.basket')->getBasket();
        if (null == $basket->getDeliveryProviderId()) {
            return $this->redirectToRoute('azimut_shop_delivery');
        }

        $site = $this->getSite($request);
        $this->get('azimut_shop.basket')->resetBasketStatus();

        return $this->render('SiteLayout/'.($site->getLayout()->getShopSummaryTemplate()?:'shop_summary.html.twig'), [
            'siteLayout'      => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle'       => $this->get('translator')->trans('summary'),
            'pageDescription' => '',
            'site'            => $site,
            'basket'          => $basket,
        ]);
    }
}
