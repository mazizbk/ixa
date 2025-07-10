<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-04 09:24:18
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;
use Azimut\Bundle\FrontofficeSecurityBundle\Form\Type\FrontofficeUserType;
use Azimut\Bundle\FormExtraBundle\Form\Type\SubmitOrCancelType;

/**
 * @PreAuthorize("isAuthenticated() && isAuthorized('APP_FRONTOFFICE_SECURITY')")
 */
class BackofficeController extends Controller
{
    public function userFormAction()
    {
        $form = $this->createForm(FrontofficeUserType::class, null, [
            'with_access_rights' => true,
            'with_password'      => true,
        ])
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutFrontofficeSecurityBundle:Backoffice:user_form.angularjs.twig', [
            'form' => $form->createView(),
        ]);
    }

    public function userUpdateFormAction()
    {
        $form = $this->createForm(FrontofficeUserType::class, null, [
            'with_access_rights' => true,
        ])
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutFrontofficeSecurityBundle:Backoffice:user_form.angularjs.twig', [
            'form' => $form->createView(),
        ]);
    }
}
