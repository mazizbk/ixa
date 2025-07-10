<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-10-09 14:28:56
 */

namespace Azimut\Bundle\CmsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_CMS') || isAuthorized('APP_CMS_*')")
 */
class ApiTrashBinController extends FOSRestController
{
    /**
     * Get all action
     * @var Request $request
     * @return array
     *
     * @Rest\View(serializerGroups={"list_trash_bin"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  resource=true,
     *  description="Cms : Get all cms files in trash bin"
     * )
     * @Rest\QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getTrashbinAction(Request $request, $locale = null)
    {
        TranslationProxy::setDefaultLocale($locale);
        $em = $this->getDoctrine()->getManager();

        $cmsFiles = $em->getRepository('AzimutCmsBundle:CmsFile')
            ->findTrashed();

        return array(
            'cmsFiles' => $cmsFiles
        );
    }

    /**
     * Delete action
     * @return View
     * @ApiDoc(
     *   section="CMS",
     *   description="Cms : Delete trash bin"
     * )
     */
    public function deleteTrashbinAction()
    {
        $em = $this->getDoctrine()->getManager();

        $em->getRepository('AzimutCmsBundle:CmsFile')
            ->deleteTrashed();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
