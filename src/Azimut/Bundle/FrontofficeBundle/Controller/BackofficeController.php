<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-10 16:33:09
 */

namespace Azimut\Bundle\FrontofficeBundle\Controller;

use Azimut\Bundle\FormExtraBundle\Form\Type\SubmitOrCancelType;
use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Form\Type\MenuType;
use Azimut\Bundle\FrontofficeBundle\Form\Type\PageType;
use Azimut\Bundle\FrontofficeBundle\Form\Type\SiteType;
use Azimut\Bundle\FrontofficeBundle\Form\Type\SiteLayoutType;
use Azimut\Bundle\FrontofficeBundle\Form\Type\PageLayoutType;
use Azimut\Bundle\FrontofficeBundle\Form\Type\ZoneCmsFileAttachmentType;
use Azimut\Bundle\FrontofficeBundle\Form\Type\ZoneType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Azimut\Bundle\FrontofficeBundle\Entity\Menu;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Azimut\Bundle\FrontofficeBundle\Entity\SiteLayout;
use Azimut\Bundle\FrontofficeBundle\Entity\PageLayout;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_FRONTOFFICE')")
 */
class BackofficeController extends Controller
{
    public function siteFormAction()
    {
        $options = [];
        if (!$this->isGranted('SUPER_ADMIN')) {
            $options['disabled'] = true;
        }

        $form = $this->createForm(SiteType::class, new Site(), $options);

        if ($this->isGranted('SUPER_ADMIN')) {
            $form->add('submit', SubmitOrCancelType::class);
        }

        return $this->render('AzimutFrontofficeBundle:Backoffice:site_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function menuFormAction()
    {
        $form = $this->createForm(MenuType::class, new Menu())
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutFrontofficeBundle:Backoffice:menu_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function pageFormAction($type)
    {
        $page = $this->getDoctrine()
            ->getRepository(Page::class)
            ->createInstanceFromString($type)
        ;

        $form = $this->createForm(PageType::class, $page)
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutFrontofficeBundle:Backoffice:page_form.angularjs.twig', array(
            'form' => $form->createView(),
            'type' => $type
        ));
    }

    public function zoneFormAction()
    {
        $form = $this->createForm(ZoneType::class, new Zone())
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutFrontofficeBundle:Backoffice:zone_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function zoneCmsFileAttachmentFormAction()
    {
        $form = $this->createForm(ZoneCmsFileAttachmentType::class);

        return $this->render('AzimutFrontofficeBundle:Backoffice:zone_cms_file_attachment_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function siteLayoutFormAction()
    {
        $form = $this->createForm(SiteLayoutType::class, new SiteLayout());
        $form->add('submit', SubmitOrCancelType::class);

        return $this->render('AzimutFrontofficeBundle:Backoffice:site_layout_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function pageLayoutFormAction()
    {
        $form = $this->createForm(PageLayoutType::class, new PageLayout());
        $form->add('submit', SubmitOrCancelType::class);

        return $this->render('AzimutFrontofficeBundle:Backoffice:page_layout_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }
}
