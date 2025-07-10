<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-30 10:32:19
 */


namespace Azimut\Bundle\FrontofficeSecurityBundle\Form\Type;

use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use EWZ\Bundle\RecaptchaBundle\Form\Type\EWZRecaptchaType;
use EWZ\Bundle\RecaptchaBundle\Validator\Constraints\IsTrue as RecaptchaTrue;

class FrontofficeUserRegistrationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('email', EmailType::class, [
                'label' => 'email',
            ])
            ->add('firstName', TextType::class, [
                'label' => 'first.name',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'last.name',
            ])
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'first_options'  => ['label' => 'password'],
                'second_options' => ['label' => 'confirm.password'],
            ])
            // ->add('recaptcha', EWZRecaptchaType::class, [
            //     'label'       => false,
            //     'mapped'      => false,
            //     'constraints' => [
            //         new RecaptchaTrue()
            //     ]
            // ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FrontofficeUser::class,
        ]);
    }
}
