<?php
/**
 * Created by mikaelp on 31-Jul-18 11:41 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\PostalAddress;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PostalAddressType extends AbstractType
{
    /**
     * @param FormBuilderInterface $builder
     * @param array $options
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('line1', TextType::class, [
                'label' => 'montgolfiere.backoffice.common.postal_address.line1',
                'required' => $options['required'],
            ])
            ->add('line2', TextType::class, [
                'label' => 'montgolfiere.backoffice.common.postal_address.line2'
            ])
            ->add('postalCode', TextType::class, [
                'label' => 'montgolfiere.backoffice.common.postal_address.postal_code',
                'required' => $options['required'],
            ])
            ->add('city', TextType::class, [
                'label' => 'montgolfiere.backoffice.common.postal_address.city',
                'required' => $options['required'],
            ])
            ->add('country', ChoiceType::class, [
                'label' => 'montgolfiere.backoffice.common.postal_address.country',
                'multiple' => false,
                'expanded' => false,
                'choices' => array_flip(Intl::getRegionBundle()->getCountryNames()),
                'preferred_choices' => [
                    'FR', 'GF', 'PF', 'TF',
                ],
                'choice_translation_domain' => false,
            ])
        ;
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => PostalAddress::class,
            'label' => false,
            'required' => false,
        ));
    }
}
