<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-09 14:45:13
 */

namespace Azimut\Bundle\DemoExternalAppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTinymceConfigType;
use Azimut\Bundle\MediacenterBundle\Form\Type\MediaDeclinationAttachmentEntityType;
use Azimut\Bundle\FormExtraBundle\Form\Type\SubmitOrCancelType;
use Azimut\Bundle\CmsBundle\Entity\CmsFileArticle;
use Azimut\Bundle\CmsBundle\Form\Type\CmsFileArticleType;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_DEMO_DEMO_EXTERNAL_APP')")
 */
class DemoController extends Controller
{
    public function indexAction()
    {
        // A form using Mediacenter widget
        $formMediacenter = $this->createFormBuilder()
            ->add('mediaDeclination', MediaDeclinationAttachmentEntityType::class)
            ->add('submit', SubmitOrCancelType::class)
            ->getForm()
        ;

        // A basic form without interaction with AngularJS
        $formBasic = $this->createFormBuilder()
            ->add('title', I18nTextType::class, [
                'label' => 'title',
                'required' => false,
            ])
            ->add('text', I18nTinymceConfigType::class, [
                'label' => 'text',
                'i18n_childen_options' => [
                    'attr' => [
                        'rows'  => 20,
                    ],
                ],
            ])
            ->getForm()
        ;

        $article = new CmsFileArticle();
        $article
            ->setTitle('My article', 'en')
            ->setTitle('Mon article', 'fr')
            ->setText('<p>Demo</p><p>## media-declination-1 | {"width":"207","height":"138"} ##</p>', 'en')
            ->setText('<p>Démo</p><p>## media-declination-2 | {"width":"207","height":"138"} ##</p>', 'fr')
        ;
        $cmsFileArticleForm = $this->createForm(CmsFileArticleType::class, $article);

        return $this->render('AzimutDemoExternalAppBundle::index.html.twig', array(
            'formMediacenter' => $formMediacenter->createView(),
            'formBasic' => $formBasic->createView(),
            'cmsFileArticleForm' => $cmsFileArticleForm->createView(),
        ));
    }

    public function catchAllAction($slug, Request $request)
    {
        return $this->render('AzimutDemoExternalAppBundle::catch_all.html.twig', [
            'slug' => $slug,
            'queryString' => $request->getQueryString()
        ]);
    }
}
