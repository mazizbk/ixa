<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-09-18 15:36:55
 */

namespace Azimut\Bundle\MediacenterBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_MEDIACENTER')")
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
     *  section="Mediacenter",
     *  resource=true,
     *  description="Mediacenter : Get all files and folders in trash bin"
     * )
     */
    public function getTrashbinAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $medias = $em->getRepository('AzimutMediacenterBundle:Media')
            ->findRootTrashed();

        $folders = $em->getRepository('AzimutMediacenterBundle:Folder')
            ->findRootTrashed();

        //return $folders;
        return array(
            'medias' => $medias,
            'folders' => $folders
        );
    }

    /**
     * Delete action
     * @return View
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Delete trash bin"
     * )
     */
    public function deleteTrashbinAction()
    {
        $em = $this->getDoctrine()->getManager();

        $medias = $em->getRepository('AzimutMediacenterBundle:Media')
            ->findTrashed();
        foreach ($medias as $media) {
            $em->remove($media);
        }

        $folders = $em->getRepository('AzimutMediacenterBundle:Folder')
            ->findTrashed();
        foreach ($folders as $folder) {
            $em->remove($folder);
        }

        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }
}
