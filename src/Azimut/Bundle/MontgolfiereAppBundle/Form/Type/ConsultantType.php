<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\ClientContact;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Consultant;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ConsultantType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'label' => 'first.name',
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'label' => 'last.name',
            ])
            ->add('email', EmailType::class, [
                'required' => false,
                'label' => 'email',
            ])
            ->add('plainPassword', PasswordType::class, [
                'label' => 'password',
            ])
        ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Consultant::class
        ));
    }

}
