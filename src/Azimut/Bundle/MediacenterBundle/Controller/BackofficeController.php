<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-07
 */

namespace Azimut\Bundle\MediacenterBundle\Controller;

use Azimut\Bundle\FormExtraBundle\Form\Type\SubmitOrCancelType;
use Azimut\Bundle\MediacenterBundle\Form\Type\MediaDeclinationType;
use Azimut\Bundle\MediacenterBundle\Form\Type\MediaType;
use Azimut\Bundle\MediacenterBundle\Form\Type\SimpleMediaType;
use Azimut\Bundle\MediacenterBundle\Form\Type\EmbedHtmlMediaType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_MEDIACENTER')")
 */
class BackofficeController extends Controller
{
    public function widgetAction()
    {
        return $this->render('AzimutMediacenterBundle:Backoffice:base_widget.html.twig');
    }

    public function mediaFormWithOneDeclinationAction($type)
    {
        $media = $this->getDoctrine()
            ->getRepository(Media::class)
            ->createInstanceFromString($type)
        ;

        $mediaDeclination = $this->getDoctrine()
            ->getRepository(MediaDeclination::class)
            ->createInstanceFromString($type)
        ;

        $media->addMediaDeclination($mediaDeclination);

        $form = $this->createForm(MediaType::class, $media, array('with_one_declination' => true, 'hide_declination_name' => true))
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutMediacenterBundle:Backoffice:media_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function mediaFormWithDeclinationsAction($type)
    {
        $media = $this->getDoctrine()
            ->getRepository(Media::class)
            ->createInstanceFromString($type)
        ;

        $form = $this->createForm(MediaType::class, $media, array('with_declinations' => true))//, 'hide_declination_name' => true));
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutMediacenterBundle:Backoffice:media_form_with_declinations.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function mediaFormAction($type)
    {
        $media = $this->getDoctrine()
            ->getRepository(Media::class)
            ->createInstanceFromString($type)
        ;

        $form = $this->createForm(MediaType::class, $media)
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutMediacenterBundle:Backoffice:media_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function mediaDeclinationFormAction($type)
    {
        $mediaDeclination = $this->getDoctrine()->getRepository(MediaDeclination::class)->createInstanceFromString($type);
        $form = $this->createForm(MediaDeclinationType::class, $mediaDeclination)
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutMediacenterBundle:Backoffice:media_declination_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function simpleMediaFormAction()
    {
        $form = $this->createForm(SimpleMediaType::class)
            ->add('submit', SubmitType::class)
        ;

        return $this->render('AzimutMediacenterBundle:Backoffice:simple_media_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function embedHtmlMediaFormAction()
    {
        $form = $this->createForm(EmbedHtmlMediaType::class)
            ->add('submit', SubmitType::class)
        ;

        return $this->render('AzimutMediacenterBundle:Backoffice:embed_html_media_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }
}
