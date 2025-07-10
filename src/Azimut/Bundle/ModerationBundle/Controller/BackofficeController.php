<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 11:52:55
 */

namespace Azimut\Bundle\ModerationBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Bundle\ModerationBundle\Entity\CmsFileBuffer;
use Azimut\Bundle\ModerationBundle\Form\Type\CmsFileBufferType;
use Azimut\Bundle\FormExtraBundle\Form\Type\SubmitOrCancelType;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_MODERATION')")
 */
class BackofficeController extends Controller
{
    public function cmsFileBufferFormAction($type)
    {
        $cmsFileBuffer = $this->getDoctrine()->getRepository(CmsFileBuffer::class)->createInstanceFromString($type);

        $form = $this->createForm(CmsFileBufferType::class, $cmsFileBuffer, [
            'with_user_email' => false,
            'with_file' => false,
            'with_captcha' => false,
        ]);
        $form->add('submit', SubmitOrCancelType::class);

        return $this->render('AzimutModerationBundle:Backoffice:cms_file_buffer_form.angularjs.twig', [
            'form' => $form->createView(),
        ]);
    }
}
