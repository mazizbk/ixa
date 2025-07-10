<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-10 16:33:09
 */

namespace Azimut\Bundle\SecurityBundle\Controller;

use Azimut\Bundle\FormExtraBundle\Form\Type\SubmitOrCancelType;
use Azimut\Bundle\SecurityBundle\Entity\Group;
use Azimut\Bundle\SecurityBundle\Entity\User;
use Azimut\Bundle\SecurityBundle\Form\Type\GroupAccessRightType;
use Azimut\Bundle\SecurityBundle\Form\Type\GroupType;
use Azimut\Bundle\SecurityBundle\Form\Type\UserAccessRightType;
use Azimut\Bundle\SecurityBundle\Form\Type\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

/**
 *  @PreAuthorize("isAuthenticated() && isAuthorized('APP_SECURITY')")
 */
class BackofficeController extends Controller
{
    public function userFormAction()
    {
        $form =
            $this->createForm(UserType::class, null, array(
                'isnew' => true
            ))
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutSecurityBundle:Backoffice:user_form.angularjs.twig', array(
            'form' => $form->createView(),
            'isnew' => true,
        ));
    }

    public function userUpdateFormAction()
    {
        $form = $this->createForm(UserType::class);

        return $this->render('AzimutSecurityBundle:Backoffice:user_form.angularjs.twig', array(
            'form' => $form->createView(),
            'isnew' => false,
            'base_url_login' => $this->getParameter('base_url_login'),
        ));
    }

    public function groupFormAction()
    {
        $form = $this->createForm(GroupType::class);
        $form->add('submit', SubmitOrCancelType::class);

        return $this->render('AzimutSecurityBundle:Backoffice:group_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function groupUpdateFormAction()
    {
        $form = $this->createForm(GroupType::class);

        return $this->render('AzimutSecurityBundle:Backoffice:group_form.angularjs.twig', array(
            'form' => $form->createView(),
        ));
    }

    public function userAccessRightFormAction()
    {
        $form = $this->createForm(UserAccessRightType::class, new User(), ['include_access_rights' => false])
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutSecurityBundle:Backoffice:access_right_list.angularjs.twig', array(
            'form' => $form->createView(),
            'userOrGroup' => 'user',
        ));
    }

    public function groupAccessRightFormAction()
    {
        $form = $this->createForm(GroupAccessRightType::class, new Group(), ['include_access_rights' => false])
            ->add('submit', SubmitOrCancelType::class)
        ;

        return $this->render('AzimutSecurityBundle:Backoffice:access_right_list.angularjs.twig', array(
            'form' => $form->createView(),
            'userOrGroup' => 'group',
        ));
    }
}
