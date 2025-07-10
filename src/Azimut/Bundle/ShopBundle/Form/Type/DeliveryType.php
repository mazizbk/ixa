<?php

/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-11-08 12:13:37
 */

namespace Azimut\Bundle\ShopBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\RadioType;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\Extension\Core\DataMapper\RadioListMapper;

use Azimut\Bundle\ShopBundle\Service\Delivery\DeliveryProviderChain;
use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\ShopBundle\Form\Type\DeliveryChoiceType;

class DeliveryType extends AbstractType
{
    /**
     * @var DeliveryProviderChain
     */
    private $deliveryProviderChain;

    /**
     * @param DeliveryProviderChain $deliveryProviderChain
     */
    public function __construct(DeliveryProviderChain $deliveryProviderChain)
    {
        $this->deliveryProviderChain = $deliveryProviderChain;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Create choice list from providers
        $choiceListData = [];
        foreach ($this->deliveryProviderChain->getProviders() as $deliveryProvider) {
            if ($deliveryProvider->isAvailableForOrder($options['order'])) {
                $shippingCost = $deliveryProvider->getShippingCost($options['order']);

                $choiceListData[$deliveryProvider->getName()] = $deliveryProvider->getId();

                $choicesInfos[$deliveryProvider->getId()] = [
                    'description'  => $deliveryProvider->getDescription(),
                    'image'        => $deliveryProvider->getImage(),
                    'shippingCost' => $shippingCost,
                ];
            }
        }

        $builder
            ->add('delivery', DeliveryChoiceType::class, [
                'choices' => $choiceListData,
                'expanded' => true,
                'choices_infos' => $choicesInfos,
            ])
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'order' => null,
            ])
            ->setAllowedTypes('order', [ Order::class ])
        ;
    }
}
