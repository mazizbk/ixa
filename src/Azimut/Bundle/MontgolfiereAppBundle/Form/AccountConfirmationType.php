<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Form;

use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccountConfirmationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('plainPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => true,
                'first_options' => [
                    'label' => 'montgolfiere.frontoffice.account_creation.password',
                ],
                'second_options' => [
                    'label' => 'montgolfiere.frontoffice.account_creation.password2',
                ]
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => FrontofficeUser::class,
        ]);
    }

}
