<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-07 15:08:54
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Controller\Annotations as Rest;
use FOS\RestBundle\Controller\Annotations\QueryParam;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\View\View;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

use Azimut\Bundle\FrontofficeBundle\Form\Type\ZoneType;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\FrontofficeBundle\Form\Type\ZoneCmsFileAttachmentType;
use Azimut\Bundle\ModerationBundle\Entity\CmsFileBuffer;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_FRONTOFFICE')")
 */
class ApiZoneController extends FOSRestController
{
    /**
     * Get all action
     * @return array
     *
     * @Rest\View(serializerGroups={"list_zones"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  resource=true,
     *  description="Frontoffice : Get all zones"
     * )
     */
    public function getZonesAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Zone[] $zones */
        $zones = $em->getRepository(Zone::class)->findAll();
        $finalZones = [];
        foreach ($zones as $zone) {
            if ($this->isGranted('VIEW', $zone->getPage())) {
                $finalZones[] = $zone;
            }
        }

        return array(
            //FIXME list_zones is never used in Entity\Zone
            'zones' => $this->get('azimut_security.filter')->serializeGroup($finalZones, ['list_zones']),
        );
    }

    /**
     * Get action
     * @param int  $id
     * @param null $locale
     * @return array
     * @internal param int $id Id of the zone
     * @Rest\View(serializerGroups={"detail_zone","detail_attached_cms_file"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Get zone"
     * )
     * @QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getZoneAction($id, $locale = null)
    {
        TranslationProxy::setDefaultLocale($locale);
        $em = $this->getDoctrine()->getManager();

        $zone = $this->getZoneEntity($id);
        $page = $zone->getPage();

        if (!$this->isGranted('VIEW', $page)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $waitingCmsFilesBufferCount = $em->getRepository(CmsFileBuffer::class)->countWaitingInTargetZone($zone);

        return array(
            'zone' => $zone,
            'waitingCmsFilesBufferCount' => $waitingCmsFilesBufferCount,
        );
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the zone
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_zone","detail_attached_cms_file"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Update zone",
     *  input="zone",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\Zone"
     * )
     */
    public function putZoneAction(Request $request, $id)
    {
        TranslationProxy::setDefaultLocale('all');
        $zone = $this->getZoneEntity($id);
        $page = $zone->getPage();
        if (!$this->isGranted('EDIT', $page)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $form = $this->createForm(ZoneType::class, $zone, array(
            'method' => 'PUT',
            'csrf_protection' => false
        ));

        return $this->updateZone($request, $zone, $form);
    }

    /**
     * Patch action
     * @var Request $request
     * @var integer $id Id of the zone
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_zone","detail_attached_cms_file"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Update zone",
     *  input="zone",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\Zone"
     * )
     */
    public function patchZoneAction(Request $request, $id)
    {
        TranslationProxy::setDefaultLocale('all');
        $zone = $this->getZoneEntity($id);
        $page = $zone->getPage();
        if (!$this->isGranted('EDIT', $page)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $form = $this->createForm(ZoneType::class, $zone, array(
            'method' => 'PATCH',
            'csrf_protection' => false
        ));

        return $this->updateZone($request, $zone, $form);
    }

    /**
     * Post action
     * @var Request $request
     * @return View|array
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Create a new zone CMS file attachment",
     *  input="zone_cms_file_attachment",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment"
     * )
     */
    public function postZonecmsfileattachmentsAction(Request $request)
    {
        $zoneCmsFileAttachment = new ZoneCmsFileAttachment();
        $form = $this->createForm(ZoneCmsFileAttachmentType::class, $zoneCmsFileAttachment, array(
            'csrf_protection' => false
        ));

        if ($form->handleRequest($request)->isValid()) {
            $page = $zoneCmsFileAttachment->getZone()->getPage();
            if (!$this->isGranted('EDIT', $page)) {
                throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
            }

            $em = $this->getDoctrine()->getManager();

            $existingAttachment = $em->getRepository(ZoneCmsFileAttachment::class)
                ->findOneByZoneAndCmsFile(
                    $zoneCmsFileAttachment->getZone()->getId(),
                    $zoneCmsFileAttachment->getCmsFile()->getId()
                )
            ;

            if (null !== $existingAttachment) {
                return $this->view(null, Response::HTTP_NO_CONTENT);
            }

            //default display order
            if (null === $zoneCmsFileAttachment->getDisplayOrder()) {
                //insert new attachment on top of zone
                $zoneCmsFileAttachment->setDisplayOrder(1);
                //insert new attachment on bottom of zone
                //$zoneCmsFileAttachment->setDisplayOrder($em->getRepository('AzimutFrontofficeBundle:ZoneCmsFileAttachment')->getMaxDisplayOrderInZone($zoneCmsFileAttachment->getZone()->getId())+1);

                //update display order of all the other attachments
                $this->updateZoneCmsFileAttachmentDisplayOrder($zoneCmsFileAttachment);
            }

            $em->persist($zoneCmsFileAttachment);
            $em->flush();

            return $this->redirectView(
                $this->generateUrl('azimut_frontoffice_api_get_zonecmsfileattachment', [
                    'id'     => $zoneCmsFileAttachment->getId(),
                    'locale' => 'all',
                ])
            );
        }

        return [
            'form' => $form,
        ];
    }

    /**
     * Get action
     * @var integer $id Id of the zone CMS file attachment
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_attached_cms_file","list_cms_files"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Get zone CMS file attachment"
     * )
     * @QueryParam(
     *  name="locale", requirements="[a-z]{2}|all", strict=true, nullable=true,
     *  description="language (ex: 'en')"
     * )
     */
    public function getZonecmsfileattachmentAction($id, $locale = null)
    {
        TranslationProxy::setDefaultLocale($locale);

        $zoneCmsFileAttachment = $this->getZoneCmsFileAttachmentEntity($id);
        $page = $zoneCmsFileAttachment->getZone()->getPage();
        if (!$this->isGranted('EDIT', $page)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        return array(
            'zoneCmsFileAttachment' => $zoneCmsFileAttachment,
        );
    }

    /**
     * Put action
     * @var Request $request
     * @var integer $id Id of the zone CMS file attachment
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_attached_cms_file","list_cms_files"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Update zone CMS file attachment",
     *  input="zone_cms_file_attachment",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment"
     * )
     */
    /*public function putZonecmsfileattachmentAction(Request $request, $id)
    {
        $zoneCmsFileAttachment = $this->getZoneCmsFileAttachmentEntity($id);

        $oldDisplayOrder = $zoneCmsFileAttachment->getDisplayOrder();

        $form = $this->createForm(ZoneCmsFileAttachmentType::class, $zoneCmsFileAttachment, array(
            'method' => 'PUT',
            'csrf_protection' => false
        ));

        if($form->handleRequest($request)->isValid()) {

            $em->flush();

            return array(
                'zoneCmsFileAttachment' => $zoneCmsFileAttachment
            );
        }

        return array(
            'form' => $form,
        );
    }*/

    /**
     * Patch action
     * @var Request $request
     * @var integer $id Id of the zone CMS file attachment
     * @return array
     *
     * @Rest\View(serializerGroups={"detail_attached_cms_file","list_cms_files"})
     *
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Update zone CMS file attachment",
     *  input="zone_cms_file_attachment",
     *  output="Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment"
     * )
     */
    public function patchZonecmsfileattachmentAction(Request $request, $id)
    {
        $zoneCmsFileAttachment = $this->getZoneCmsFileAttachmentEntity($id);
        $page = $zoneCmsFileAttachment->getZone()->getPage();
        if (!$this->isGranted('EDIT', $page)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $em = $this->getDoctrine()->getManager();

        $oldDisplayOrder = $zoneCmsFileAttachment->getDisplayOrder();

        $form = $this->createForm(ZoneCmsFileAttachmentType::class, $zoneCmsFileAttachment, array(
            'method' => 'PATCH',
            'csrf_protection' => false
        ));

        $form->handleRequest($request);

        $this->updateZoneCmsFileAttachmentDisplayOrder($zoneCmsFileAttachment, $oldDisplayOrder);

        if ($form->isValid()) {
            $em->flush();

            return array(
                'zoneCmsFileAttachment' => $zoneCmsFileAttachment
            );
        }

        return array(
            'form' => $form,
        );
    }

    protected function updateZoneCmsFileAttachmentDisplayOrder(ZoneCmsFileAttachment $zoneCmsFileAttachment, $oldDisplayOrder = null)
    {
        $em = $this->getDoctrine()->getManager();
        $zone = $zoneCmsFileAttachment->getZone();
        $page = $zone->getPage();
        if (!$this->isGranted('EDIT', $page)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        //if oldDisplayOrder is null, it is an new attachment, consider max display order (+1 for the one we are adding) as old value
        if (null === $oldDisplayOrder) {
            $oldDisplayOrder = $em->getRepository(ZoneCmsFileAttachment::class)->getMaxDisplayOrderInZone($zone->getId())+1;
        }

        //update order (only section between old and new displayOrder)
        $startDisplayOrder = $oldDisplayOrder;
        $endDisplayOrder = $zoneCmsFileAttachment->getDisplayOrder();

        //displayOrder has been increased
        if ($endDisplayOrder > $startDisplayOrder) {
            for ($i=$startDisplayOrder+1;$i<=$endDisplayOrder;$i++) {
                $zoneCmsFileAttachmentPropagate = $em->getRepository(ZoneCmsFileAttachment::class)->findOneByZoneAndDisplayOrder($zone->getId(), $i);
                //if the next display order does not exist then we reach the limit, we will insert element here
                if (null == $zoneCmsFileAttachmentPropagate) {
                    $zoneCmsFileAttachment->setDisplayOrder($i-1);
                    break;
                } else {
                    $zoneCmsFileAttachmentPropagate->setDisplayOrder($zoneCmsFileAttachmentPropagate->getDisplayOrder()-1);
                }
            }
        }
        //displayOrder has been decreased
        else {
            for ($i=$startDisplayOrder-1;$i>=$endDisplayOrder;$i--) {
                $zoneCmsFileAttachmentPropagate = $em->getRepository(ZoneCmsFileAttachment::class)->findOneByZoneAndDisplayOrder($zone->getId(), $i);
                //if the next display order does not exist then we reach the limit, we will insert element here
                if (null == $zoneCmsFileAttachmentPropagate) {
                    $zoneCmsFileAttachment->setDisplayOrder($i+1);
                } else {
                    $zoneCmsFileAttachmentPropagate->setDisplayOrder($zoneCmsFileAttachmentPropagate->getDisplayOrder()+1);
                }
            }
        }
    }

    /**
     * Private : get zone entity instance
     * @var integer $id Id of the entity
     * @return Zone
     */
    protected function getZoneEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $zone = $em->getRepository(Zone::class)->find($id);

        if (!$zone) {
            throw $this->createNotFoundException('Unable to find zone '.$id);
        }

        return $zone;
    }

    /**
     * Delete action
     * @var integer $id Id of the zone CMS file attachment
     * @return View
     * @ApiDoc(
     *  section="Frontoffice",
     *  description="Frontoffice : Delete zone CMS file attachment"
     * )
     */
    public function deleteZonecmsfileattachmentAction($id)
    {
        $zoneCmsFileAttachment = $this->getZoneCmsFileAttachmentEntity($id);

        $em = $this->getDoctrine()->getManager();
        $repository = $em->getRepository(ZoneCmsFileAttachment::class);
        $zone = $zoneCmsFileAttachment->getZone();

        if (!$zone->isAllowDeleteAttachments()) {
            throw $this->createAccessDeniedException("Attachments in this zone can't be removed");
        }

        $page = $zone->getPage();
        if (!$this->isGranted('EDIT', $page)) {
            throw $this->createAccessDeniedException($this->get('translator')->trans('action.not.allowed'));
        }

        $zoneCmsFileAttachmentDisplayOrder = $zoneCmsFileAttachment->getDisplayOrder();
        $maxDisplayOrder = $repository->getMaxDisplayOrderInZone($zone->getId());

        $em->remove($zoneCmsFileAttachment);

        $em->flush();

        return $this->view(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Private : get zoneCmsFileAttachment entity instance
     * @var integer $id Id of the entity
     * @return ZoneCmsFileAttachment
     */
    protected function getZoneCmsFileAttachmentEntity($id)
    {
        $em = $this->getDoctrine()->getManager();

        $zoneCmsFileAttachment = $em->getRepository(ZoneCmsFileAttachment::class)->find($id);

        if (!$zoneCmsFileAttachment) {
            throw $this->createNotFoundException('Unable to find zoneCmsFileAttachment '.$id);
        }

        return $zoneCmsFileAttachment;
    }

    protected function updateZone($request, $zone, FormInterface $form)
    {
        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return array(
                'zone' => $zone
            );
        }

        return array(
            'form' => $form,
        );
    }
/*
    protected function updateZoneCmsFileAttachment($request,$zoneCmsFileAttachment,$form)
    {
        $em = $this->getDoctrine()->getManager();
        $em->persist($zoneCmsFileAttachment);

        $zone = $zoneCmsFileAttachment->getZone();

        $startDisplayOrder = zoneCmsFileAttachment.displayOrder;
        $endDisplayOrder = newDisplayOrder;

        //$zone = $zoneCmsFileAttachment->getZone();
        //$displayOrder = $zoneCmsFileAttachment->getDisplayOrder();

        //while ($zoneCmsFileAttachmentAtDisplayOrder = $em->getRepository('AzimutFrontofficeBundle:ZoneCmsFileAttachment')->findOneByZoneAndDisplayOrder($zone->getId(), $displayOrder)) {
        //    $displayOrder++;
        //    $zoneCmsFileAttachmentAtDisplayOrder->setDisplayOrder($displayOrder);
        //    $em->persist($zoneCmsFileAttachmentAtDisplayOrder);
        //}

        if ($form->isValid()) {

            $em->flush();

            return array(
                'zoneCmsFileAttachment' => $zoneCmsFileAttachment
            );
        }

        return array(
            'form' => $form,
        );
    }
*/
}
