<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-10-04 15:56:36
 */

namespace Azimut\Component\Address\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CountryType;

use Azimut\Component\Address\Entity\BaseAddress;

class BaseAddressType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('line1', null, [
                'label' => 'address',
            ])
            ->add('line2', null, [
                'label' => false,
            ])
            ->add('postalCode', null, [
                'label' => 'postal.code',
            ])
            ->add('city', null, [
                'label' => 'city',
            ])
            ->add('country', CountryType::class, [
                'label' => 'country',
                'data' => 'FR', // use France as default value
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BaseAddress::class,
        ]);
    }
}
