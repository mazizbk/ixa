<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-07-28 10:52:01
 */

namespace Azimut\Bundle\FrontofficeCustomBundle\Controller;

use Azimut\Bundle\FrontofficeBundle\Controller\AbstractFrontController;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;

class ContactFormController extends AbstractFrontController
{
    public function simpleFormAction(Request $originalRequest)
    {
        $form = $this->getContactForm();

        if ($form->handleRequest($originalRequest)->isValid()) {
            $data = $form->getData();

            $from = $data['email'];
            $sender = $this->container->getParameter('sender_address');
            $to = $this->container->getParameter('contact_form_recipient');
            $name = $data['name'];
            $message = $data['message'];

            $message = \Swift_Message::newInstance()
                ->setSubject('Contact depuis le site jeparticipe.workcare.fr')
                ->setSender($sender)
                ->setFrom($sender)
                ->setReplyTo($from)
                ->setTo($to)
                ->setBody($this->renderView('Emails/FrontofficeCustom/simple_form_email.txt.twig', [
                    'name'    => $name,
                    'email'   => $from,
                    'message' => $message
                ]))
            ;
            $this->get('mailer')->send($message);

            // Re-create the form, emptying the fields
            $form = $this->getContactForm();

            return $this->render('Forms/FrontofficeCustom/simple_form.html.twig', [
                'form' => $form->createView(),
                'messageSent' => true,
            ]);
        }

        return $this->render('Forms/FrontofficeCustom/simple_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function getContactForm()
    {
        $form = $this->createFormBuilder(null, ['action' => '#contact'])
             ->add('name', TextType::class, [
                 'label' => false,
                 'attr' => [
                     'placeholder' => 'your.name',
                 ],
             ])
             ->add('email', EmailType::class, [
                 'label' => false,
                 'attr' => [
                     'placeholder' => 'your.email',
                 ],
             ])
             ->add('message', TextareaType::class, [
                 'label' => false,
                 'attr' => [
                     'placeholder' => 'your.message',
                     'rows' => 10,
                 ],
             ])
             ->add('recaptcha', EWZRecaptchaType::class, [
                 'mapped' => false,
                 'label' => false,
                 'constraints' => [
                     new RecaptchaTrue()
                 ]
             ])
             ->add('send', SubmitType::class, [
                 'label' => 'send.message',
                 'attr' => [
                     'class' => 'btn-primary'
                 ]
             ])
             ->getForm()
        ;

        return $form;
    }
}
