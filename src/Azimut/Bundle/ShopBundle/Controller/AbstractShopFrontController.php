<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-11-09 10:19:20
 */

namespace Azimut\Bundle\ShopBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;

use Azimut\Bundle\FrontofficeBundle\Controller\AbstractFrontController;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;

class AbstractShopFrontController extends AbstractFrontController
{
    /**
     * Check if site supports shop
     */
    protected function checkActiveShopOnSite(Site $site)
    {
        if (!$site->hasShop()) {
            throw $this->createNotFoundException("Shop is not enabled on this site.");
        }

        if (!$site->hasUserLogin()) {
            throw $this->createNotFoundException("User login is not enabled on this site.");
        }
    }

    /**
     * Check overall requirements and return redirection if necessary
     */
    protected function getPreRedirection(Request $request)
    {
        $site = $this->getSite($request);
        $this->checkActiveShopOnSite($site);

        // if requested domain name is not site's main domain, redirect
        if (null != $mainDomainRedirection = $this->getMainDomainRedirection($site, $request)) {
            return $mainDomainRedirection;
        }
    }

    /**
     * If user not logged in, return redirection to purchase tunnel account login form
     */
    protected function getAnonymousUserRedirection(Request $request)
    {
        if (!$this->getUser() instanceof UserInterface) {
            return $this->redirectToRoute('azimut_shop_account_login');
        }
    }
}
