<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-14 14:54:43
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Symfony\Component\HttpFoundation\Response;

/**
 * @PreAuthorize("isAuthenticated()")
 */
class DashboardController extends Controller
{
    public function widgetsAction()
    {
        if (!$this->isGranted('APP_FRONTOFFICE')) {
            return new Response();
        }

        $sitesCount = $this->getDoctrine()->getRepository(Site::class)->getSitesCount();

        return $this->render('AzimutFrontofficeBundle:Backoffice:dashboard_widgets.angularjs.twig', [
            'sitesCount' => $sitesCount,
        ]);
    }
}
