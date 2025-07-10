<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-13 10:26:53
 */

namespace Azimut\Bundle\DemoPaymentBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class PaymentDemoType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('cardNumber', TextType::class, [
                'label' => 'card.number',
            ])
            // For demo purpose, this form embed the amount instead of storing it in DB
            ->add('amount', HiddenType::class, [
                'data' => $options['amount'],
            ])
            ->add('order_id', HiddenType::class, [
                'data' => $options['order_id'],
            ])
            ->add('submit', SubmitType::class)
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'amount' => null,
            'order_id' => null,
        ]);
    }
}
