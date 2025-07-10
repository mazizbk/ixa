<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-01 16:49:12
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Azimut\Bundle\FrontofficeBundle\Form\Type\ZoneCmsFileAttachmentType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment;

class DevController extends Controller
{
    public function indexAction()
    {
        return $this->render('AzimutFrontofficeBundle:Dev:index.html.twig');
    }

    public function formZoneCmsFileAttachementAction(Request $request)
    {
        $attachment = new ZoneCmsFileAttachment();
        $form = $this->createForm(ZoneCmsFileAttachmentType::class, $attachment)
            ->add('addAttachement', SubmitType::class)
        ;

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($attachment);

            $em->flush();
        }

        return $this->render('AzimutFrontofficeBundle:Dev:form_zone_cms_file_attachment.html.twig', array(
            'form' => $form->createView()
        ));
    }
}
