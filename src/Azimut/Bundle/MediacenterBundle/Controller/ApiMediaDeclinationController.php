<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-10-31
 */

namespace Azimut\Bundle\MediacenterBundle\Controller;

use Azimut\Bundle\MediacenterBundle\Form\Type\MediaDeclinationType;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_MEDIACENTER')")
 */
class ApiMediaDeclinationController extends FOSRestController
{
    /**
     * Get all action
     * @return array
     *
     * @Rest\View(serializerGroups={"list_media_declinations"})
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  resource=true,
     *  description="Mediacenter : Get all media declinations of a media"
     * )
     */
    public function getMediadeclinationsAction()
    {
        //do not permit to fetch all declinations without specifying a media
        //return $this->view(null, 403);

        $em = $this->getDoctrine()->getManager();

        $mediaDeclinations = $em->getRepository('AzimutMediacenterBundle:MediaDeclination')
            ->findAll();

        return array(
            'mediaDeclinations' => $mediaDeclinations,
        );
    }

    /**
     * Get action
     * @var integer $id Id of the media declination
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_media_declination"})
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Get media declination"
     * )
     */
    public function getMediadeclinationAction($id)
    {
        $mediaDeclination = $this->getMediaDeclinationEntity($id);

        return array(
            'mediaDeclination' => $mediaDeclination
        );
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Create new media declination. Caution : media declination type is dynamic, see media declination type list for complete input capabilities",
     *  input="media_declination",
     *  output="Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination"
     * )
     */
    public function postMediadeclinationsAction(Request $request)
    {
        if (!$request->request->get('media_declination')) {
            throw new HttpException(400, "mediaDeclination not found in posted datas.");
        }

        $repository = $this->getDoctrine()->getRepository('AzimutMediacenterBundle:MediaDeclination');

        $type = $request->request->get('media_declination')['type'];

        $mediaDeclination = $repository->createInstanceFromString($type);

        $form = $this->createForm(MediaDeclinationType::class, $mediaDeclination, array(
            'with_media_id' => true,
            'csrf_protection' => false,
            'validation_groups' => array_merge(
                ['Default'],
                $this->get('azimut_mediacenter.validation_group_resolver')->getGroups($request)
            )
        ));


        if ($form->handleRequest($request)->isValid()) {

            //check if a file or folder has the same name
            if ($newName = $this->checkExistingName($mediaDeclination)) {
                $mediaDeclination->setName($newName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($mediaDeclination);
            $em->flush();

            return $this->redirectView(
                $this->generateUrl(
                    'azimut_mediacenter_api_get_mediadeclination',
                    array('id' => $mediaDeclination->getId())
                    //TODO : propagate _format to the get request array('id' => $mediaDeclination->getId(),'_format' => $_format)
                )
            );
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the media declination
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_media_declination"})
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Update media declination. Caution : media declination type declination is dynamic, see media declination type list for complete input capabilities",
     *  input="media_declination",
     *  output="Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination"
     * )
     */
    public function putMediadeclinationAction(Request $request, $id)
    {
        if (!$request->request->get('media_declination')) {
            throw new HttpException(400, "mediaDeclination not found in posted datas.");
        }

        $mediaDeclination = $this->getMediaDeclinationEntity($id);
        $form = $this->createForm(MediaDeclinationType::class, $mediaDeclination, array(
            'method' => 'PUT',
            'with_media_id' => true,
            'allow_form_extra_data' => true,
            'csrf_protection' => false,
            'validation_groups' => array_merge(
                ['Default'],
                $this->get('azimut_mediacenter.validation_group_resolver')->getGroups($request, $mediaDeclination::getMediaDeclinationType())
            )
        ));

        return $this->updateMediaDeclination($request, $mediaDeclination, $form);
    }

    /**
     * Patch action
     * @var Request $request
     * @var integer $id Id of the media declination
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_media_declination"})
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Update media declination (only fields that are submitted). Caution : media declination type is dynamic, see media declination type list for complete input capabilities",
     *  input="media_declination",
     *  output="Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination"
     * )
     */
    public function patchMediadeclinationAction(Request $request, $id)
    {
        $mediaDeclination = $this->getMediaDeclinationEntity($id);
        $form = $this->createForm(MediaDeclinationType::class, $mediaDeclination, array(
            'method' => 'PATCH',
            'with_media_id' => true,
            'csrf_protection' => false
        ));

        return $this->updateMediaDeclination($request, $mediaDeclination, $form);
    }

    /**
     * Delete action
     * @var integer $id Id of the media declination
     * @return View
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Delete media declination"
     * )
     */
    public function deleteMediadeclinationAction($id)
    {
        $mediaDeclination = $this->getMediaDeclinationEntity($id);

        $em = $this->getDoctrine()->getManager();
        $em->remove($mediaDeclination);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Private : get media declination entity instance
     * @var integer $id Id of the entity
     * @return MediaDeclination
     */
    protected function getMediaDeclinationEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $mediaDeclination = $em->getRepository('AzimutMediacenterBundle:MediaDeclination')->find($id);

        if (!$mediaDeclination) {
            throw $this->createNotFoundException('Unable to find media declination '.$id);
        }

        return $mediaDeclination;
    }

    protected function updateMediaDeclination($request, $mediaDeclination, $form)
    {
        if ($form->handleRequest($request)->isValid()) {

            //check if a declination has the same name
            if ($newName = $this->checkExistingName($mediaDeclination)) {
                $mediaDeclination->setName($newName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return array(
                'mediaDeclination' => $mediaDeclination
            );
        }

        return array(
            'form' => $form,
        );
    }

    //TODO : Folder has an equivalent function, refactor ?
    protected function checkExistingName($mediaDeclination)
    {
        $name = $mediaDeclination->getName();
        $excludeId = $mediaDeclination->getId();
        $mediaId = $mediaDeclination->getMediaId();

        $em = $this->getDoctrine()->getManager();
        $mediaDeclinationRepository = $em->getRepository('AzimutMediacenterBundle:MediaDeclination');

        $i = 0;
        $newName = $name;

        do {
            $mediaDeclination = $mediaDeclinationRepository->findOneByNameInMedia($newName, $mediaId);

            //exclude current media declination if it is an update
            if (null !== $mediaDeclination && $mediaDeclination->getId() == $excludeId) {
                $mediaDeclination = null;
            }

            if ($mediaDeclination) {
                $i++;
                $newName = "$name ($i)";
            }
        } while ($mediaDeclination);

        return $newName;
    }
}
