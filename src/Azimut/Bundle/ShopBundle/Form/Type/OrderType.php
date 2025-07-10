<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-14 16:27:04
 */

namespace Azimut\Bundle\ShopBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Azimut\Bundle\FormExtraBundle\Form\Type\DatePickerType;

use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\ShopBundle\Entity\DeliveryTracking;
use Azimut\Bundle\ShopBundle\Service\OrderStatusProvider;
use Azimut\Bundle\ShopBundle\Form\Type\DeliveryTrackingType;

class OrderType extends AbstractType
{
    /**
     * @var OrderStatusProvider
     */
    private $statusProvider;

    public function __construct(OrderStatusProvider $statusProvider)
    {
        $this->statusProvider = $statusProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('status', ChoiceType::class, [
                'label'    => 'status',
                'choices'  => array_flip($this->statusProvider->getStatuses())
            ])
            ->add('paymentDate', DatePickerType::class, [
                'label'    => 'payment.date',
            ])
            ->add('privateComment', TextareaType::class, [
                'label'    => 'private.comment',
                'attr'     => ['rows' => '10'],
                'required' => false,
            ])
            ->add('deliveryTrackings', CollectionType::class, array(
                'label'         => 'delivery.trackings',
                'entry_type'    => DeliveryTrackingType::class,
                'allow_add'     => true,
                'allow_delete'  => true,
                'entry_options' => ['label' => false],
                'by_reference'  => false, // disable replacement of collection and uses add/remove methods
                'required'      => false,
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Order::class,
        ]);
    }
}
