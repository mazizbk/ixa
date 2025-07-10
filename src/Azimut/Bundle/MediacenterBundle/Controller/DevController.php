<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-22 09:54:38
 */

namespace Azimut\Bundle\MediacenterBundle\Controller;

use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Azimut\Bundle\MediacenterBundle\Form\Type\MediaDeclinationEntityType;
use Azimut\Bundle\MediacenterBundle\Form\Type\MediaDeclinationType;
use Azimut\Bundle\MediacenterBundle\Form\Type\MediaType;
use Azimut\Bundle\MediacenterBundle\Form\Type\SimpleMediaType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

class DevController extends Controller
{
    public function indexAction()
    {
        return $this->render('AzimutMediacenterBundle:Dev:index.html.twig');
    }

    public function formMediaAction(Request $request)
    {
        TranslationProxy::setDefaultLocale('fr');
        $media = $this->getDoctrine()
            ->getRepository('AzimutMediacenterBundle:Media')
            ->createInstanceFromString('image')
        ;

        $media->setName('test media');
        $media->setFolder($this->getDoctrine()->getRepository('AzimutMediacenterBundle:Folder')->find(1));

        $form = $this->createForm(MediaType::class, $media)
            ->add('submit', SubmitType::class)
        ;

        $created_media = null;

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($media);

            $em->flush();

            $created_media = $media;
        }

        return $this->render('AzimutMediacenterBundle:Dev:form_media.html.twig', array(
            'form' => $form->createView(),
            'created_media' => $created_media
        ));
    }

    public function formMediaSimpleAction(Request $request)
    {
        $media = new Media();
        $media->setFolder($this->getDoctrine()->getRepository('AzimutMediacenterBundle:Folder')->find(1));

        $form = $this->createForm(SimpleMediaType::class, $media)
            ->add('submit', SubmitType::class)
        ;

        $created_media = null;

        if ($form->handleRequest($request)->isValid()) {
            if (empty($request->files->get('simpleMedia')['upload'])) {
                throw new \Exception("No file sended or file too big.", 1);
            }

            $media = $form->getData();

            $em = $this->getDoctrine()->getManager();
            $em->persist($media);

            $em->flush();

            $created_media = $media;
        }

        return $this->render('AzimutMediacenterBundle:Dev:form_media.html.twig', array(
            'form' => $form->createView(),
            'created_media' => $created_media
        ));
    }

    public function formMediaWithOneDeclinationAction(Request $request)
    {
        TranslationProxy::setDefaultLocale('fr');

        $media = $this->getDoctrine()
            ->getRepository('AzimutMediacenterBundle:Media')
            ->createInstanceFromString('image')
        ;
        $media_declination = $this->getDoctrine()
            ->getRepository('AzimutMediacenterBundle:MediaDeclination')
            ->createInstanceFromString('image')
        ;

        $media->setName('test media');
        $media->setFolder($this->getDoctrine()->getRepository('AzimutMediacenterBundle:Folder')->find(1));

        $media->addMediaDeclination($media_declination);

        $form = $this->createForm(MediaType::class, $media, array('with_one_declination' => true))
            ->add('submit', SubmitType::class)
        ;

        $created_media = null;

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($media);

            $em->flush();

            $created_media = $media;
        }

        return $this->render('AzimutMediacenterBundle:Dev:form_media.html.twig', array(
            'form' => $form->createView(),
            'created_media' => $created_media
        ));
    }

    public function testMediaSelectionAction(Request $request)
    {
        $form = $this->get('form.factory')->createBuilder()
            ->add('choice', MediaDeclinationEntityType::class)
            ->add('submit', SubmitType::class)
            ->getForm()
        ;

        if ($form->handleRequest($request)->isValid()) {
            $data = $form->getData()['choice'];
            if ($data) {
                echo "<strong>Déclinaison de média choisie</strong><br />";
                echo "<strong>Nom</strong>: ".$data->getName();
                echo "<hr />";
            } else {
                echo '<em>no media chosen</em>';
            }
        }

        return $this->render('AzimutMediacenterBundle:Dev:testMediaSelection.html.twig', array('form' => $form->createView()));
    }

    public function formMediaWithDeclinationsAction(Request $request)
    {
        TranslationProxy::setDefaultLocale('fr');

        $media = $this->getDoctrine()
            ->getRepository('AzimutMediacenterBundle:Media')
            ->createInstanceFromString('image')
        ;

        $media->setName('test media');
        $media->setFolder($this->getDoctrine()->getRepository('AzimutMediacenterBundle:Folder')->find(1));

        $form = $this->createForm(MediaType::class, $media, array('with_declinations' => true))
            ->add('submit', SubmitType::class)
        ;

        $created_media = null;

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($media);

            $em->flush();

            $created_media = $media;

            echo '$created_media::getMediaType() : '.$created_media::getMediaType().'<br />';

            foreach ($created_media->getMediaDeclinations() as $key => $declination) {
                echo
                    $declination->getName().'<br />'.
                    $declination::getMediaDeclinationType().'<br />'
                ;
            }
        }

        return $this->render('AzimutMediacenterBundle:Dev:form_media.html.twig', array(
            'form' => $form->createView(),
            'allow_add_declinations' => true,
            'created_media' => $created_media
        ));
    }

    public function formMediaDeclinationAction(Request $request)
    {
        /*$em = $this->getDoctrine()->getManager();
        $media_declination = $em->getRepository('AzimutMediacenterBundle:Media')->find(4);*/

        $media_declination = $this->getDoctrine()
            ->getRepository('AzimutMediacenterBundle:MediaDeclination')
            ->createInstanceFromString('image')
        ;

        //$media_declination->setName('test media_declination');

        //$form = $this->createForm(new MediaType(), $media_declination);
        $form = $this->createForm(MediaDeclinationType::class, $media_declination)
            ->add('submit', SubmitType::class)
        ;

        if ($form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            //$em->persist($media);
            //$em->persist($media_declination);
            //$media_declination->upload();

            $em->persist($media_declination);
            $em->flush();
        }

        return $this->render('AzimutMediacenterBundle:Dev:form_media_declination.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}
