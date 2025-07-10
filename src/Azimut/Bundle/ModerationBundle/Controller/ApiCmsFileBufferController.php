<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 15:04:31
 */

namespace Azimut\Bundle\ModerationBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\View\View;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\HttpFoundation\File\File;

use Azimut\Bundle\ModerationBundle\Entity\CmsFileBuffer;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment;
use Azimut\Bundle\ModerationBundle\Form\Type\CmsFileBufferType;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_MODERATION')")
 */
class ApiCmsFileBufferController extends FOSRestController
{
    /**
     * Get action
     * @return array
     *
     * @ApiDoc(
     *  section="Moderation",
     *  description="Moderation : Get available cms file buffer types"
     * )
     */
    public function getCmsfilesbufferAvailabletypesAction()
    {
        $types = $this->getDoctrine()
            ->getRepository(CmsFileBuffer::class)
            ->getAvailableTypes()
        ;

        return [
            'types' => $types,
        ];
    }

    /**
     * Get all action
     * @var Request $request
     * @return array
     *
     * @Rest\View(serializerGroups={"always", "list_cms_files_buffer"})
     *
     * @ApiDoc(
     *  section="Moderation",
     *  resource=true,
     *  description="Moderation : Get all cms files buffer"
     * )
     */
    public function getCmsfilesbufferAction()
    {
        $em = $this->getDoctrine()->getManager();

        $cmsFilesBuffer = $em->getRepository(CmsFileBuffer::class)->findBy([
            'isArchived' => false,
        ]);

        return [
            'cmsFilesBuffer' => $cmsFilesBuffer,
        ];
    }

    /**
     * Get action
     * @var integer $id Id of the cms file buffer
     * @return array
     *
     * @Rest\View(serializerGroups={"always", "detail_cms_file_buffer"})
     *
     * @ApiDoc(
     *  section="Moderation",
     *  description="Moderation : Get cms file buffer"
     * )
     */
    public function getCmsfilebufferAction($id)
    {
        $cmsFileBuffer = $this->getCmsFileBufferEntity($id);

        $cmsFileBufferFileMimeType = null;
        if ($cmsFileBuffer->getFilePath()) {
            $file = new File($this->getParameter('uploads_dir').'/moderation/'.$cmsFileBuffer->getFilePath());
            $cmsFileBufferFileMimeType = $file->getMimeType();
        }

        return [
            'cmsFileBuffer' => $cmsFileBuffer,
            'cmsFileBufferFileMimeType' => $cmsFileBufferFileMimeType,
        ];
    }

    /**
     * Delete action
     * @var integer $id Id of the cms file buffer
     * @return View
     * @ApiDoc(
     *  section="Moderation",
     *  description="Moderation : Delete cms file buffer"
     * )
     */
    public function deleteCmsfilebufferAction($id)
    {
        $cmsFileBuffer = $this->getCmsFileBufferEntity($id);
        $em = $this->getDoctrine()->getManager();

        $cmsFileBuffer->isArchived(true);

        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Private : get cms file buffer entity instance
     * @var integer $id Id of the entity
     * @return CmsFileBuffer
     */
    protected function getCmsFileBufferEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $cmsFileBuffer = $em->getRepository(CmsFileBuffer::class)->find($id);

        if (!$cmsFileBuffer) {
            throw $this->createNotFoundException('Unable to find cms file buffer '.$id);
        }

        return $cmsFileBuffer;
    }

    /**
     * Convert a cmsfile buffer to a real cmsfile
     * @var integer $id Id of the entity
     * @return array
     *
     * @Rest\View(serializerGroups={"always","detail_cms_file","detail_media_declination_attachments"})
     *
     * @ApiDoc(
     *  section="Moderation",
     *  description="Moderation : Convert a cmsfile buffer to a real cmsfile.",
     *  input="Azimut\Bundle\ModerationBundle\Form\Type\CmsFileBufferType",
     *  output="Azimut\Bundle\CmsBundle\Entity\CmsFile"
     * )
     */
    public function postConvertcmsfilebufferAction(Request $request, $id)
    {
        TranslationProxy::setDefaultLocale('all');

        $cmsFileBuffer = $this->getCmsFileBufferEntity($id);

        if ($cmsFileBuffer->isArchived()) {
            throw new BadRequestHttpException("This CMS file buffer has already been processed.");
        }

        if (!$this->isGranted('EDIT', $cmsFileBuffer::getTargetCmsFileClass())) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $form = $this->createForm(CmsFileBufferType::class, $cmsFileBuffer, [
            // 'method' => 'POST',
            'with_user_email' => false,
            'csrf_protection' => false,
            'with_captcha' => false,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            // Convert CmsFileBuffer to CmsFile
            $cmsFile = $this->convertCmsFileBuffer($cmsFileBuffer);

            $em->flush();

            return [
                'cmsFile' => $cmsFile,
            ];
        }

        return [
            'form' => $form,
        ];
    }

    private function convertCmsFileBuffer(CmsFileBuffer $cmsFileBuffer)
    {
        $em = $this->getDoctrine()->getManager();

        $targetZone = $cmsFileBuffer->getTargetZone();

        if (null != $targetZone) {
            if (!$this->isGranted('EDIT', $targetZone->getPage())) {
                throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
            }
        }

        $cmsFile = $this->container->get('azimut_moderation.cms_file_buffer_converter')->convert($cmsFileBuffer);

        $contentUrl = null;

        if (null != $targetZone) {
            $zoneCmsFileAttachment = new ZoneCmsFileAttachment();
            $zoneCmsFileAttachment
                ->setCmsFile($cmsFile)
                ->setZone($targetZone)
            ;
            $em->persist($zoneCmsFileAttachment);

            TranslationProxy::setDefaultLocale($cmsFileBuffer->getLocale());

            $context = $this->get('router')->getContext();
            $context->setHost($targetZone->getPage()->getSite()->getMainDomainName());

            $contentUrl = $this->generateUrl('azimut_frontoffice', [
                'path' => $targetZone->getPage()->getFullSlug($cmsFileBuffer->getLocale()),
                '_locale' => $cmsFileBuffer->getLocale(),
            ], UrlGeneratorInterface::ABSOLUTE_URL);

            if ($targetZone->getZoneDefinition()->hasStandaloneCmsfilesRoutes()) {
                $contentUrl .= '/'.$cmsFile->getSlug($cmsFileBuffer->getLocale());
            }
        }

        // set mail to author
        $this->get('azimut_moderation.mailer')->sendUserCmsFileBufferValidated($cmsFileBuffer->getUserEmail(), $cmsFileBuffer->getUserLocale(), $cmsFileBuffer->getDomainName(), $cmsFile, $cmsFileBuffer, $contentUrl);

        return $cmsFile;
    }
}
