<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-14 14:57:35
 */

namespace Azimut\Bundle\CmsContactBundle\Controller;

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
        if (!$this->isGranted('APP_CMS_CONTACT')) {
            return new Response();
        }

        $contactsCount = $this->getDoctrine()->getRepository(CmsFile::class)->getCmsFilesCountByType('contact');

        return $this->render('AzimutCmsContactBundle:Backoffice:dashboard_widgets.angularjs.twig', [
            'contactsCount' => $contactsCount,
        ]);
    }
}
