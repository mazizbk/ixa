<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-10-04 16:11:13
 */

namespace Azimut\Bundle\ShopBundle\Form\Type;

use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;

use Azimut\Component\Address\Form\Type\BaseAddressType;
use Azimut\Bundle\ShopBundle\Entity\OrderAddress;

class OrderAddressType extends BaseAddressType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', TextType::class, [
                'label' => 'first.name',
            ])
            ->add('lastName', TextType::class, [
                'label' => 'last.name',
            ])
        ;

        parent::buildForm($builder, $options);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => OrderAddress::class,
        ]);
    }
}
