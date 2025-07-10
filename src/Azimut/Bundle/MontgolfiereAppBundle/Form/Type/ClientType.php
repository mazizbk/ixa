<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ClientType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('corporateName', TextType::class, [
                'required' => true,
                'label' => 'montgolfiere.backoffice.clients.fields.corporate_name',
            ])
            ->add('tradingName', TextType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.clients.fields.trading_name',
            ])
            ->add('questionName', TextType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.clients.fields.question_name',
            ])
            ->add('clientStatus', ChoiceType::class, [
                'label' => 'montgolfiere.backoffice.clients.fields.client_type',
                'required' => true,
                'choices' => [
                    'montgolfiere.backoffice.clients.fields.client_type_values.client' => Client::STATUS_CLIENT,
                    'montgolfiere.backoffice.clients.fields.client_type_values.prospect' => Client::STATUS_PROSPECT,
                    'montgolfiere.backoffice.clients.fields.client_type_values.former_client' => Client::STATUS_FORMER_CLIENT,
                ]
            ])
            ->add('postalAddress', PostalAddressType::class, [
                'label' => 'montgolfiere.backoffice.clients.fields.postal_address',
                'required' => false,
            ])
            ->add('workforce', NumberType::class, [
                'label' => 'montgolfiere.backoffice.clients.fields.workforce',
                'required' => false,
            ])
            ->add('legalStatus', TextType::class, [
                'label' => 'montgolfiere.backoffice.clients.fields.legal_status',
                'required' => false,
            ])
            ->add('activity', TextType::class, [
                'label' => 'montgolfiere.backoffice.clients.fields.activity',
                'required' => false,
            ])
            ->add('NAFCode', TextType::class, [
                'label' => 'montgolfiere.backoffice.clients.fields.NAF_code',
                'required' => false,
            ])
            ->add('turnover', MoneyType::class, [
                'label' => 'montgolfiere.backoffice.clients.fields.turnover',
                'required' => false,
                'grouping' => true,
                'scale' => 0,
            ])
            ->add('website', UrlType::class, [
                'label' => 'montgolfiere.backoffice.clients.fields.website',
                'required' => false,
            ])
            ->add('comments', TextareaType::class, [
                'label' => 'montgolfiere.backoffice.clients.fields.comments',
                'required' => false,
            ])
            ->add('uploadedFile', FileType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.clients.fields.logo',
                'constraints' => [
                    new File(['mimeTypes' => ['image/*'], 'mimeTypesMessage' => 'montgolfiere.backoffice.common.please_select_an_image'])
                ],
            ])
        ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Client::class,
        ]);
    }

}
