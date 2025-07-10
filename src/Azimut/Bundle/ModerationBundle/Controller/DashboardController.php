<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 11:52:55
 */

namespace Azimut\Bundle\ModerationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Symfony\Component\HttpFoundation\Response;

use Azimut\Bundle\ModerationBundle\Entity\CmsFileBuffer;

/**
 * @PreAuthorize("isAuthenticated()")
 */
class DashboardController extends Controller
{
    public function widgetsAction()
    {
        if (!$this->isGranted('APP_MODERATION')) {
            return new Response();
        }

        $waitingCmsFilesBufferCount = $this->getDoctrine()->getRepository(CmsFileBuffer::class)->countWaiting();

        return $this->render('AzimutModerationBundle:Backoffice:dashboard_widgets.angularjs.twig', [
            'waitingCmsFilesBufferCount' => $waitingCmsFilesBufferCount,
        ]);
    }
}
