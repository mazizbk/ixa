<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-10 15:26:07
 */


namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractFrontController extends Controller
{
    protected function getMainDomainRedirection(Site $site, Request $request)
    {
        $mainDomainName = $site->getMainDomainName();
        if ($mainDomainName->getName() !== $request->getHost()) {
            return $this->redirect(str_replace(
                '://'.$request->getHost(),
                '://'.$mainDomainName->getName(),
                $request->getUri()
            ), 301);
        }

        return null;
    }

    /**
     * Request argument is no longer necessary as it is fetched by the Front service itself
     * (keeping it for backward compatibility)
     */
    protected function getSite(Request $request = null)
    {
        $site = $this->get('azimut_frontoffice.front')->getCurrentSite();
        if (!$site) {
            throw $this->createNotFoundException(sprintf('No site found for domain "%s".', $request->getHost()));
        }
        return $site;
    }
}
