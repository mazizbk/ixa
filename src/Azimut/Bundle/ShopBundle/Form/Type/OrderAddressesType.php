<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-10-04 18:28:45
 */

namespace Azimut\Bundle\ShopBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

use Azimut\Bundle\ShopBundle\Entity\Order;

class OrderAddressesType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('billingAddress', OrderAddressType::class, [
                'label' => 'billing.address',
            ])
            ->add('deliveryAddress', OrderAddressType::class, [
                'label' => 'delivery.address',
            ])
            ->add('clientComment', TextareaType::class, [
                'label'    => 'comment',
                'attr'     => ['rows' => '10'],
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
