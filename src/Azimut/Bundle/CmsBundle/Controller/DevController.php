<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-20 16:06:14
 */

namespace Azimut\Bundle\CmsBundle\Controller;

use Azimut\Bundle\CmsBundle\Form\Type\CmsFileType;
use Symfony\Component\HttpFoundation\Request;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class DevController extends Controller
{
    public function indexAction()
    {
        return $this->render('AzimutCmsBundle:Dev:index.html.twig');
    }

    public function cmsContentAction()
    {
        $em = $this->getDoctrine()->getManager();

        $cmsFiles = $em->getRepository('AzimutCmsBundle:CmsFile')
            ->findAll();

        return $this->render('AzimutCmsBundle:Dev:cms_content.html.twig', array(
            'cmsFiles' => $cmsFiles,
        ));
    }

    public function formCmsFileAction(Request $request)
    {
        $cmsFile = $this->getDoctrine()
            ->getRepository('AzimutCmsBundle:CmsFile')
            ->createInstanceFromString('article')
        ;

        $cmsFile->setTitle('test article');

        $form = $this->createForm(CmsFileType::class, $cmsFile)
            ->add('create file', 'submit')
        ;

        $createdCmsFile = null;

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($cmsFile);

            $em->flush();

            $createdCmsFile = $cmsFile;
        }

        return $this->render('AzimutCmsBundle:Dev:form_cms_file.html.twig', array(
            'form' => $form->createView(),
            'createdCmsFile' => $createdCmsFile
        ));
    }

    public function formCmsFileWithAttachmentsAction(Request $request)
    {
        $cmsFile = $this->getDoctrine()
            ->getRepository('AzimutCmsBundle:CmsFile')
            ->createInstanceFromString('article')
        ;

        $cmsFile->setTitle('test article');

        $form = $this->createForm(CmsFileType::class, $cmsFile, array('with_attachments' => true))
            ->add('submit', 'submit')
        ;

        $createdCmsFile = null;

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($cmsFile);

            $em->flush();

            $createdCmsFile = $cmsFile;

            echo '$createdCmsFile->getCmsFileType() : '.$createdCmsFile->getCmsFileType().'<br />';

            foreach ($createdCmsFile->getAttachments() as $key => $attachment) {
                echo
                    $attachment->getMediaDeclination()->getName().'<br />'
                ;
            }
        }

        return $this->render('AzimutCmsBundle:Dev:form_cms_file.html.twig', array(
            'form' => $form->createView(),
            'allowAddAttachments' => true,
            'createdCmsFile' => $createdCmsFile
        ));
    }
}
