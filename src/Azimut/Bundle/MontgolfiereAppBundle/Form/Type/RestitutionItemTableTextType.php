<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\RestitutionItemTableText;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RestitutionItemTableTextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('longSignification', $options['long_signification_hidden']?HiddenType::class:TextareaType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.settings.item_restitution.long_signification',
            ])
            ->add('shortSignification', TextareaType::class, [
                'required' => false,
                'label' => 'montgolfiere.backoffice.settings.item_restitution.short_signification',
                'attr' => [
                    'rows' => 5,
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => RestitutionItemTableText::class,
            ])
            ->setRequired('long_signification_hidden')
            ->setAllowedTypes('long_signification_hidden', 'boolean')
        ;
    }

}
