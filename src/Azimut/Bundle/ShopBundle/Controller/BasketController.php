<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-10-03 09:26:09
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Azimut\Bundle\FrontofficeBundle\Entity\Site;

class BasketController extends AbstractShopFrontController
{
    public function indexAction(Request $request)
    {
        if ($preRedirection = $this->getPreRedirection($request)) {
            return $preRedirection;
        }

        $site = $this->getSite($request);
        $this->get('azimut_shop.basket')->resetBasketStatus();

        return $this->render('SiteLayout/'.($site->getLayout()->getBasketTemplate()?:'basket.html.twig'), [
            'siteLayout'      => 'SiteLayout/'.$site->getTemplate(),
            'pageTitle'       => $this->get('translator')->trans('basket'),
            'pageDescription' => '',
            'site'            => $site,
            'basket'          => $this->get('azimut_shop.basket')->getBasket(),
        ]);
    }
}
