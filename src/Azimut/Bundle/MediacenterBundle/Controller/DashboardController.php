<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-14 14:32:25
 */

namespace Azimut\Bundle\MediacenterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Symfony\Component\HttpFoundation\Response;

/**
 * @PreAuthorize("isAuthenticated()")
 */
class DashboardController extends Controller
{
    public function widgetsAction()
    {
        if (!$this->isGranted('APP_MEDIACENTER')) {
            return new Response();
        }

        $mediaCount = $this->getDoctrine()->getRepository(Media::class)->getMediaCount();

        return $this->render('AzimutMediacenterBundle:Backoffice:dashboard_widgets.angularjs.twig', [
            'mediaCount' => $mediaCount,
        ]);
    }
}
