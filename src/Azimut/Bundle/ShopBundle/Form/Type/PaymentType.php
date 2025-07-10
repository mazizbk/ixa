<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-11 11:26:58
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

use Azimut\Bundle\ShopBundle\Service\Payment\PaymentProviderChain;
use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\ShopBundle\Form\Type\PaymentChoiceType;

class PaymentType extends AbstractType
{
    /**
     * @var PaymentProviderChain
     */
    private $paymentProviderChain;

    /**
     * @param PaymentProviderChain $paymentProviderChain
     */
    public function __construct(PaymentProviderChain $paymentProviderChain)
    {
        $this->paymentProviderChain = $paymentProviderChain;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        // Create choice list from providers
        $choiceListData = [];
        foreach ($this->paymentProviderChain->getProviders() as $paymentProvider) {
            if ($paymentProvider->isAvailableForOrder($options['order'])) {
                $choiceListData[$paymentProvider->getName()] = $paymentProvider->getId();

                $choicesInfos[$paymentProvider->getId()] = [
                    'description'  => $paymentProvider->getDescription(),
                    'image'        => $paymentProvider->getImage(),
                ];
            }
        }

        $builder
            ->add('payment', PaymentChoiceType::class, [
                'choices'       => $choiceListData,
                'expanded'      => true,
                'choices_infos' => $choicesInfos,
                'label'         => false
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
