<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-06 14:53:42
 */

namespace Azimut\Bundle\CmsBundle\Controller;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Symfony\Component\Security\Core\Authorization\AccessDecisionManagerInterface;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

use Azimut\Bundle\CmsBundle\Form\Type\CmsFileType;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\ModerationBundle\Entity\CmsFileBuffer;




/**
 * @PreAuthorize("isAuthenticated() && (isAuthorized('APP_CMS') || isAuthorized('APP_CMS_*') || isAuthorized('APP_FRONTOFFICE'))")
 */
class ApiCmsFileController extends FOSRestController
{
    /**
     * Get action
     * @return array
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Get available cms file types"
     * )
     * @QueryParam(
     *  name="namespace", requirements="[a-zA-Z0-9]+", strict=true, nullable=true,
     *  description="bundle namespace restriction (ex: 'CmsContact')"
     * )
     */
    public function getCmsfilesAvailabletypesAction($namespace = null)
    {
        $types = $this->getDoctrine()
            ->getRepository('AzimutCmsBundle:CmsFile')
            ->getAvailableTypes($namespace)
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
     * @Rest\View(serializerGroups={"list_cms_files"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  resource=true,
     *  description="CMS : Get all cms files"
     * )
     * @QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     * @QueryParam(
     *  name="namespace", requirements="[a-zA-Z0-9]+", strict=true, nullable=true,
     *  description="bundle namespace restriction (ex: 'CmsContact')"
     * )
     */
    public function getCmsfilesAction($namespace = null, $locale = null)
    {
        TranslationProxy::setDefaultLocale($locale);

        $em = $this->getDoctrine()->getManager();

        $repository = $em->getRepository('AzimutCmsBundle:CmsFile');

        if (null == $namespace) {
            $cmsFiles = $repository->findNotTrashed();
        } else {
            $types = $this->getDoctrine()
                ->getRepository('AzimutCmsBundle:CmsFile')
                ->getAvailableTypes($namespace)
            ;
            $cmsFiles = $repository->findNotTrashedByTypes($types);
        }

        $waitingCmsFilesBufferCount = $em->getRepository(CmsFileBuffer::class)->countWaiting();

        $cmsFiles = $this->get('azimut_security.filter')->serializeGroup($cmsFiles, ['list_cms_files']);

        $cmsFilesPublicationsCount = [];
        foreach ($cmsFiles as $key => $cmsFile) {
            $cmsFile['publicationsCount'] = $this->get('azimut_cms.cmsfile_manager')->getCmsfilePublicationsByCmsFileId($cmsFile['id']);
            $cmsFiles[$key] = $cmsFile;
        }

        return [
            'cmsFiles' => $cmsFiles,
            'waitingCmsFilesBufferCount' => $waitingCmsFilesBufferCount,
        ];
    }

    /**
     * Get action
     * @var integer $id Id of the cms file
     * @return array
     *
     * @Rest\View(serializerGroups={"always","detail_cms_file","detail_media_declination_attachments"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Get cms file"
     * )
     * @QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getCmsfileAction($id, $locale=null)
    {
        TranslationProxy::setDefaultLocale($locale);
        $cmsFile = $this->getCmsFileEntity($id);
        if (!$this->isGranted('VIEW', $cmsFile)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        return array(
            'cmsFile' => $cmsFile,
            'cmsFileEditIsGranted' => $this->isGranted('EDIT', $cmsFile)
        );
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Create new cms file. Caution : cms file type is dynamic, see cms file type list for complete input capabilities",
     *  input="cms_file",
     *  output="Azimut\Bundle\CmsBundle\Entity\CmsFile"
     * )
     */
    public function postCmsfilesAction(Request $request)
    {
        if (!$request->request->get('cms_file')) {
            throw new \InvalidArgumentException("CmsFile not found in posted datas.");
        }

        if (empty($request->request->get('cms_file')['type'])) {
            throw new \InvalidArgumentException("CmsFile type has to be defined.");
        }

        $type = $request->request->get('cms_file')['type'];

        $cmsFile = $this->getDoctrine()
            ->getRepository('AzimutCmsBundle:CmsFile')
            ->createInstanceFromString($type)
        ;

        if (!$this->isGranted('EDIT', get_class($cmsFile))) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $form = $this->createForm(CmsFileType::class, $cmsFile, array(
            'csrf_protection' => false,
        ));

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($cmsFile);
            $em->flush();

            return $this->redirectView(
                $this->generateUrl('azimut_cms_api_get_cmsfile',[
                    'id'     => $cmsFile->getId(),
                    'locale' => 'all',
                ])
            );
        }

        return array(
            'form' => $form,
        );
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the cms file
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_cms_file","list_media_declination_attachments"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Update cms file. Caution : cms file type is dynamic, see cms file type list for complete input capabilities",
     *  input="cms_file",
     *  output="Azimut\Bundle\CmsBundle\Entity\CmsFile"
     * )
     */
    public function putCmsfileAction(Request $request, $id)
    {
        TranslationProxy::setDefaultLocale('all');

        if (!$request->request->get('cms_file')) {
            throw new \InvalidArgumentException("CmsFile not found in posted datas.");
        }

        $cmsFile = $this->getCmsFileEntity($id);

        if (!$this->isGranted('EDIT', $cmsFile)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $form = $this->createForm(CmsFileType::class, $cmsFile, array(
            'method' => 'PUT',
            'allow_extra_fields' => true,
            'csrf_protection' => false,
        ));

        return $this->updateCmsFile($request, $cmsFile, $form, $id);
    }

    /**
     * Patch action
     * @var Request $request
     * @var integer $id Id of the cms file
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_cms_file"})
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Update cms file (only fields that are submitted). Caution : cms file type is dynamic, see cms file type list for complete input capabilities",
     *  input="cms_file",
     *  output="Azimut\Bundle\CmsBundle\Entity\CmsFile"
     * )
     */
    public function patchCmsfileAction(Request $request, $id)
    {
        TranslationProxy::setDefaultLocale('all');

        if (!$request->request->get('cms_file')) {
            throw new \InvalidArgumentException("CmsFile not found in posted datas.");
        }

        $cmsFile = $this->getCmsFileEntity($id);
        if (!$this->isGranted('EDIT', $cmsFile)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }
        $form = $this->createForm(CmsFileType::class, $cmsFile, array(
            'method' => 'PATCH',
            'csrf_protection' => false,
        ));

        return $this->updateCmsFile($request, $cmsFile, $form, $id);
    }

    /**
     * Delete action
     * @var integer $id Id of the cms file
     * @return View
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Delete cms file"
     * )
     */
    public function deleteCmsfileAction($id)
    {
        $cmsFile = $this->getCmsFileEntity($id);
        if (!$this->isGranted('EDIT', $cmsFile)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($cmsFile);
        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Get cmsfile publications action
     * @var integer $id Id of the cms file
     * @return array
     *
     * @ApiDoc(
     *  section="CMS",
     *  description="CMS : Get cms file publications"
     * )
     */
    public function getCmsfilePublicationsAction($id)
    {
        $cmsFile = $this->getCmsFileEntity($id);
        if (!$this->isGranted('VIEW', $cmsFile)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        return [
            'publications' => $this->get('azimut_cms.cmsfile_manager')->getCmsfilePublications($cmsFile),
        ];
    }

    /**
     * Private : get cms file entity instance
     * @var integer $id Id of the entity
     * @return CmsFile
     */
    protected function getCmsFileEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $cmsFile = $em->getRepository('AzimutCmsBundle:CmsFile')->find($id);

        if (!$cmsFile) {
            throw $this->createNotFoundException('Unable to find cms file '.$id);
        }

        return $cmsFile;
    }

    protected function updateCmsFile($request, $cmsFile, FormInterface $form, $id)
    {
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            // force doctrine to reload from DB
            // (because of some collections not being reindexed)
            $em->clear();
            $cmsFile = $this->getCmsFileEntity($id);

            return [
                'cmsFile' => $cmsFile,
            ];
        }

        return array(
            'form' => $form,
        );
    }
}
