<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-14 14:53:32
 */

namespace Azimut\Bundle\CmsBundle\Controller;

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
        if (!$this->isGranted('APP_CMS')) {
            return new Response();
        }

        $articlesCount = $this->getDoctrine()->getRepository(CmsFile::class)->getCmsFilesCountByType('article');

        $productsCount = $this->getDoctrine()->getRepository(CmsFile::class)->getCmsFilesCountByType('product');

        return $this->render('AzimutCmsBundle:Backoffice:dashboard_widgets.angularjs.twig', [
            'articlesCount' => $articlesCount,
            'productsCount' => $productsCount,
        ]);
    }
}
