<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\ClientContact;
use Misd\PhoneNumberBundle\Form\Type\PhoneNumberType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ClientContactType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'required' => true,
                'label' => 'montgolfiere.backoffice.clients.contacts.fields.first_name',
            ])
            ->add('lastName', TextType::class, [
                'required' => true,
                'label' => 'montgolfiere.backoffice.clients.contacts.fields.last_name',
            ])
            ->add('position', TextType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.clients.contacts.fields.position',
            ])
            ->add('emailAddress', EmailType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.clients.contacts.fields.email_address',
            ])
            ->add('phoneNumber', PhoneNumberType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.clients.contacts.fields.phone_number',
            ])
            ->add('isHeadOfHumanResources', CheckboxType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.clients.contacts.fields.is_head_of_hr',
            ])
        ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => ClientContact::class
        ));
    }

}
