<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-14 14:59:05
 */

namespace Azimut\Bundle\CmsMapBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * @PreAuthorize("isAuthenticated()")
 */
class DashboardController extends Controller
{
    public function widgetsAction()
    {
        if (!$this->isGranted('APP_CMS_MAP')) {
            return new Response();
        }

        $mapPointsCount = $this->getDoctrine()->getRepository(CmsFile::class)->getCmsFilesCountByType('map_point');

        return $this->render('AzimutCmsMapBundle:Backoffice:dashboard_widgets.angularjs.twig', [
                'mapPointsCount' => $mapPointsCount,
            ]);
    }
}
