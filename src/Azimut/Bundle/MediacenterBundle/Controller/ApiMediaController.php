<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-10-01
 */

namespace Azimut\Bundle\MediacenterBundle\Controller;

use Azimut\Bundle\MediacenterBundle\Form\Type\MediaType;
use Azimut\Bundle\MediacenterBundle\Form\Type\SimpleMediaType;
use Azimut\Bundle\MediacenterBundle\Form\Type\EmbedHtmlMediaType;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Azimut\Bundle\MediacenterBundle\Entity\Folder;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\View\View;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\Request;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_MEDIACENTER')")
 */
class ApiMediaController extends FOSRestController
{
    /**
     * Get action
     * @return array
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Get available media types"
     * )
     */
    public function getMediaAvailabletypesAction()
    {
        $types = $this->getDoctrine()
            ->getRepository('AzimutMediacenterBundle:Media')
            ->getAvailableTypes();

        return array(
            'types' => $types,
        );
    }

    /**
     * Get all action
     * @var Request $request
     * @return array
     *
     * @Rest\Get("/media")
     *
     * @Rest\View(serializerGroups={"list_medias"})
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  resource=true,
     *  description="Mediacenter : Get all medias"
     * )
     * @QueryParam(
     *  name="locale", requirements="([a-z]{2}|all)", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getAllMediaAction($locale = null)
    {
        TranslationProxy::setDefaultLocale($locale);

        $em = $this->getDoctrine()->getManager();

        $medias = $em->getRepository('AzimutMediacenterBundle:Media')->findNotTrashed();

        return array(
            'medias' => $this->get('azimut_security.filter')->serializeGroup($medias, ['list_medias']),
        );
    }

    /**
     * Get action
     * @var integer $id Id of the media
     * @return array
     *
     * @Rest\View(serializerGroups={"always","detail_media","detail_media_declination"})
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Get media"
     * )
     * @QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getMediaAction($id, $locale = null)
    {
        TranslationProxy::setDefaultLocale($locale);
        $media = $this->getMediaEntity($id);

        if (!$this->isGranted('VIEW', $media)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        return array(
            'media' => $media,
            'mediaEditIsGranted' => $this->isGranted('EDIT', $media)
        );
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Create new media. Caution : media type is dynamic, see media type list for complete input capabilities",
     *  input="media",
     *  output="Azimut\Bundle\MediacenterBundle\Entity\Media"
     * )
     */
    public function postMediaAction(Request $request)
    {
        if (!$request->request->get('media')) {
            throw new HttpException(400, "Media not found in posted datas.");
        }

        if (empty($request->request->get('media')['type'])) {
            throw new HttpException(400, "Media type has to be defined.");
        }

        $type = $request->request->get('media')['type'];

        $media = $this->getDoctrine()
            ->getRepository('AzimutMediacenterBundle:Media')
            ->createInstanceFromString($type)
        ;
        $mediaDeclination = $this->getDoctrine()
            ->getRepository('AzimutMediacenterBundle:MediaDeclination')
            ->createInstanceFromString($type)
        ;
        $mediaDeclination->setName("Original");
        $media->addMediaDeclination($mediaDeclination);

        $form = $this->createForm(MediaType::class, $media, array(
            'with_one_declination' => true,
            'csrf_protection' => false,
            'validation_groups' => array_merge(
                ['Default'],
                $this->get('azimut_mediacenter.validation_group_resolver')->getGroups($request)
            )
        ));

        if ($form->handleRequest($request)->isValid()) {
            /** @var Media $media */
            $media = $form->getData();

            if (!$this->isGranted('WRITE', $media->getFolder())) {
                $folderName = mb_strtolower($media->getFolder()->getName());
                if ('my.library' == $folderName) {
                    $folderName = $this->container->get('translator')->trans('my.library');
                }
                $message = $this->get('translator')->trans('security.media.creation.denied.can.not.write.%folder%', ['%folder%' => $folderName]);
                throw $this->createAccessDeniedException($message);
            }

            //check if a file or folder has the same name
            if ($newName = $this->checkExistingName($media)) {
                $media->setName($newName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($media);
            $em->flush();

            return $this->redirectView(
                $this->generateUrl(
                    'azimut_mediacenter_api_get_media',
                    array('id' => $media->getId())
                )
            );
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : post media from file"
     * )
     */
    public function postMediafromfileAction(Request $request)
    {
        if (!$request->request->get('simple_media')) {
            throw new HttpException(400, "Media not found in posted datas.");
        }

        if (empty($request->files->get('simple_media')['upload'])) {
            throw new \Exception("No file sended or file too big.", 1);
        }

        $media = new Media();

        $form = $this->createForm(SimpleMediaType::class, $media, array(
            'csrf_protection' => false
        ));

        if ($form->handleRequest($request)->isValid()) {
            /** @var Media $media */
            $media = $form->getData();

            if (!$this->isGranted('WRITE', $media->getFolder())) {
                $folderName = mb_strtolower($media->getFolder()->getName());
                if ('my.library' == $folderName) {
                    $folderName = $this->container->get('translator')->trans('my.library');
                }
                $message = $this->get('translator')->trans('security.media.creation.denied.can.not.write.%folder%', ['%folder%' => $folderName]);
                throw $this->createAccessDeniedException($message);
            }

            //check if a file or folder has the same name
            if ($newName = $this->checkExistingName($media)) {
                $media->setName($newName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($media);
            $em->flush();

            return $this->redirectView(
                $this->generateUrl('azimut_mediacenter_api_get_media',[
                    'id' => $media->getId(),
                    'locale' => 'all'
                ])
            );
        }

        return [
            'form' => $form,
        ];
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : post media from embed HTML"
     * )
     */
    public function postMediafromembedhtmlAction(Request $request)
    {
        if (!$request->request->get('embed_html_media')) {
            throw new HttpException(400, "Media not found in posted datas.");
        }

        if (empty($request->get('embed_html_media')['embed'])) {
            throw new HttpException(400, "No embed HTML provided.", 1);
        }

        $media = new Media();

        $form = $this->createForm(EmbedHtmlMediaType::class, $media, array(
            'csrf_protection' => false
        ));

        if ($form->handleRequest($request)->isValid()) {
            /** @var Media $media */
            $media = $form->getData();

            if (!$this->isGranted('WRITE', $media->getFolder())) {
                $folderName = mb_strtolower($media->getFolder()->getName());
                if ('my.library' == $folderName) {
                    $folderName = $this->container->get('translator')->trans('my.library');
                }
                $message = $this->get('translator')->trans('security.media.creation.denied.can.not.write.%folder%', ['%folder%' => $folderName]);
                throw $this->createAccessDeniedException($message);
            }

            //check if a file or folder has the same name
            if ($newName = $this->checkExistingName($media)) {
                $media->setName($newName);
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($media);
            $em->flush();

            return $this->redirectView(
                $this->generateUrl(
                    'azimut_mediacenter_api_get_media',
                    array('id' => $media->getId(), 'locale' => TranslationProxy::getDefaultLocale())
                )
            );
        }

        return array(
            'form' => $form
        );
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the media
     * @return array
     *
     * @Rest\View(serializerGroups={"always","detail_media","detail_media_declination"})
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Update media. Caution : media type is dynamic, see media type list for complete input capabilities",
     *  input="media",
     *  output="Azimut\Bundle\MediacenterBundle\Entity\Media"
     * )
     */
    public function putMediaAction(Request $request, $id)
    {
        TranslationProxy::setDefaultLocale('all');

        if (!$request->request->get('media')) {
            throw new HttpException(400, "Media not found in posted datas.");
        }

        $media = $this->getMediaEntity($id);

        if (!$this->isGranted('EDIT', $media)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        if (!empty($request->request->get('media')['mediaDeclinations'])) {
            $form = $this->createForm(MediaType::class, $media, array(
                'with_declinations' => true,
                'csrf_protection' => false,
                'method' => 'PUT',
                'validation_groups' => array_merge(
                    ['Default'],
                    $this->get('azimut_mediacenter.validation_group_resolver')->getGroups($request, $media::getMediaType())
                )
            ));
        } else {
            $form = $this->createForm(MediaType::class, $media, array(
                'csrf_protection' => false,
                'method' => 'PUT',
            ));
        }

        return $this->updateMedia($request, $media, $form);
    }

    /**
     * Patch action
     * @var Request $request
     * @var integer $id Id of the media
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_media"})
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Update media (only fields that are submitted). Caution : media type is dynamic, see media type list for complete input capabilities",
     *  input="media",
     *  output="Azimut\Bundle\MediacenterBundle\Entity\Media"
     * )
     */
    public function patchMediaAction(Request $request, $id)
    {
        if (!$request->request->get('media')) {
            throw new HttpException(400, "Media not found in posted datas.");
        }

        $media = $this->getMediaEntity($id);

        if (!$this->isGranted('EDIT', $media)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $form = $this->createForm(MediaType::class, $media, array(
            'csrf_protection' => false,
            'method' => 'PATCH',
        ));

        return $this->updateMedia($request, $media, $form);
    }

    /**
     * Delete action
     * @var integer $id Id of the media
     * @return View
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Delete media"
     * )
     */
    public function deleteMediaAction($id)
    {
        $media = $this->getMediaEntity($id);

        if (!$this->isGranted('EDIT', $media)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($media);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get media publications action
     * @var integer $id Id of the media
     * @return array
     *
     * @ApiDoc(
     *  section="Mediacenter",
     *  description="Mediacenter : Get media publications"
     * )
     */
    public function getMediaPublicationsAction($id)
    {
        $media = $this->getMediaEntity($id);

        if (!$this->isGranted('VIEW', $media)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $mediaPublications = [];
        $mediaAttachments = [];

        // Get attachments for each media declination
        foreach ($media->getMediaDeclinations() as $mediaDeclination) {

            $mediaAttachments = array_merge($mediaAttachments, $mediaDeclination->getMediaDeclinationAttachments()->toArray());
        }


        // Find media published inside cmsFile richtexts

        // @TODO: CmsFile->embeddedMediaDeclinations should use CmsFileMediaDeclinationAttachment (or a dedicated class inheriting AbstractMediaDeclinationAttachment) instead of direct link. This would avoid this specific query to find embedded media declinations.

        $em = $this->getDoctrine()->getManager();

        $cmsFilesEmbeddingMedia = $em->getRepository(CmsFile::class)->createQueryBuilder('c')
            ->innerJoin('c.embeddedMediaDeclinations', 'md')
            ->leftJoin('md.media', 'm')
            ->where('m = :media')
            ->setParameter('media', $media)
            ->getQuery()
            ->getResult()
        ;

        foreach ($cmsFilesEmbeddingMedia as $cmsFileEmbeddingMedia) {
            $mediaPublications[] = [
                'id' => null,
                'attached_object_name' => $cmsFileEmbeddingMedia->getName(),
                'attached_object_class' => get_class($cmsFileEmbeddingMedia),
                'attached_object_type_name' => 'cms.file.type.' . $cmsFileEmbeddingMedia->getCmsFileType(),
                'attached_object_id' => $cmsFileEmbeddingMedia->getId(),
            ];
        }

        // End find media published inside cmsFile richtexts

        // Format results
        foreach ($mediaAttachments as $mediaAttachment) {
            $mediaPublications[] = [
                'id' => $mediaAttachment->getId(),
                'attached_object_name' => $mediaAttachment->getAttachedObjectName(),
                'attached_object_class' => $mediaAttachment->getAttachedObjectClass(),
                'attached_object_type_name' => $mediaAttachment->getAttachedObjectTypeName(),
                'attached_object_id' => $mediaAttachment->getAttachedObject() ? $mediaAttachment->getAttachedObject()->getId() : null,
            ];
        }

        return [
            'publications' => $mediaPublications,
            'publications_count' => count($mediaPublications),
        ];
    }

    /**
     * Private : get media entity instance
     * @var integer $id Id of the entity
     * @return Media
     */
    protected function getMediaEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $media = $em->getRepository('AzimutMediacenterBundle:Media')->find($id);

        if (!$media) {
            throw $this->createNotFoundException('Unable to find media '.$id);
        }

        return $media;
    }

    protected function updateMedia($request, $media, $form)
    {
        TranslationProxy::setDefaultLocale('all');

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $folder = $media->getFolder();

            if (!$media->isTrashed() && !$folder) {
                // restore media in it's original folder path
                $parents = explode('/', $media->getTrashedFolderPath());

                $folderRepository = $em->getRepository('AzimutMediacenterBundle:Folder');

                $parentFolder = null;

                // recreate folder structure
                $parentsCount = count($parents);
                for ($i=0;$i<$parentsCount;$i++) {
                    if ($folderName = $parents[$i]) {
                        if (!$folder = $folderRepository->findOneNotTrashedByNameInFolder($folderName, $parentFolder)) {
                            $folder = new Folder();
                            $folder->setName($folderName);
                            $folder->setParentFolder($parentFolder);
                        }
                        $parentFolder = $folder;
                    }
                }
                $folder->addMedia($media);
                $media->setTrashedFolderPath(null);
            }

            //check if a file or folder has the same name
            if ($newName = $this->checkExistingName($media)) {
                $media->setName($newName);
            }

            $em->flush();

            return array(
                'media' => $media
            );
        }

        return array(
            'form' => $form,
        );
    }

    //TODO : Folder has an equivalent function, refactor ?
    protected function checkExistingName($media)
    {
        $name = $media->getName();
        $excludeId = $media->getId();
        $folderId = $media->getFolderId();

        $em = $this->getDoctrine()->getManager();
        $folderRepository = $em->getRepository('AzimutMediacenterBundle:Folder');
        $mediaRepository = $em->getRepository('AzimutMediacenterBundle:Media');

        $i = 0;
        $newName = $name;

        do {
            $folder = $folderRepository->findOneByNameInFolder($newName, $folderId);
            $media = $mediaRepository->findOneByNameInFolderExcludingMedia($newName, $folderId, $excludeId);

            if ($folder || $media) {
                $i++;
                $newName = "$name ($i)";
            }
        } while ($folder || $media);

        return $newName;
    }
}
