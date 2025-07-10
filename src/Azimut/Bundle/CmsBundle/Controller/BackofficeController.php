<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-06-25
 */

namespace Azimut\Bundle\CmsBundle\Controller;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\CmsBundle\Form\Type\CmsFileType;
use Azimut\Bundle\FormExtraBundle\Form\Type\SubmitOrCancelType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Azimut\Bundle\CmsBundle\Entity\CmsFileMediaDeclinationAttachment;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Component\PHPExtra\TraitHelper;
use Azimut\Bundle\CmsBundle\Entity\Comment;
use Azimut\Bundle\CmsBundle\Form\Type\CommentType;
use Azimut\Bundle\CmsBundle\Form\Type\ProductItemType;

/**
 * @PreAuthorize("isAuthenticated() && (isAuthorized('APP_CMS') || isAuthorized('APP_CMS_*') || isAuthorized('APP_FRONTOFFICE'))")
 */
class BackofficeController extends Controller
{
    public function fileFormAction($type)
    {
        $cmsFile = $this->getDoctrine()->getRepository(CmsFile::class)->createInstanceFromString($type);

        if (TraitHelper::isClassUsing(get_class($cmsFile), 'Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileAttachmentsTrait')) {
            $attachment = new CmsFileMediaDeclinationAttachment();
            $cmsFile->addAttachment($attachment);
        }

        $form = $this->createForm(CmsFileType::class, $cmsFile)
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutCmsBundle:Backoffice:file_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function filePreviewAction($id)
    {
        $cmsFile = $this->getDoctrine()->getRepository(CmsFile::class)->find($id);

        if (!$cmsFile) {
            throw $this->createNotFoundException('Unable to find cms file '.$id);
        }

        return $this->render('AzimutCmsBundle:Backoffice:file_preview.html.twig', [
            'cmsFile' => $cmsFile,
        ]);
    }

    public function commentFormAction($action)
    {
        $form = $this->createForm(CommentType::class, null, [
            'with_is_visible'      => true,
            'with_captcha'         => false,
            'with_hidden_cms_file' => 'create' == $action,
        ]);

        $form
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutCmsBundle:Backoffice:comment_form.angularjs.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function productItemFormAction($action)
    {
        $form = $this->createForm(ProductItemType::class, null, [
            'with_hidden_cms_file' => 'create' == $action,
        ]);

        $form
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutCmsBundle:Backoffice:product_item_form.angularjs.twig', [
            'form' => $form->createView(),
        ]);
    }
}
