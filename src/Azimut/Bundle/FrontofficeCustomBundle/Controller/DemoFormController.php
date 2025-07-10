<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-05 15:36:45
 */

namespace Azimut\Bundle\FrontofficeCustomBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

use Azimut\Bundle\FrontofficeCustomBundle\Form\Type\DemoFormType;

class DemoFormController extends Controller
{
    public function formAction(Request $originalRequest)
    {
        $form = $this->createForm(DemoFormType::class)
            ->add('send', SubmitType::class)
        ;
        $dataSent = false;

        if ($form->handleRequest($originalRequest)->isValid()) {
            $data = $form->getData();

            // Do stuff with data
            // ...

            $dataSent = true;
        }

        return $this->render('AzimutFrontofficeCustomBundle:DemoForm:form.html.twig', [
            'dataSent' => $dataSent,
            'form' => $form->createView(),
        ]);
    }
}
