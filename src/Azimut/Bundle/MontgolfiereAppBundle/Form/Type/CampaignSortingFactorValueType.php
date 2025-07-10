<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignSortingFactorValueType extends AbstractType implements HasTypeOption
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('workforce', NumberType::class)
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                if(!$data || !$data instanceof CampaignSortingFactorValue) {
                    return;
                }
                $campaign = $data->getSortingFactor()->getCampaign();

                foreach ($campaign->getAllowedLanguages() as $locale) {
                    $form
                        ->add('label_'.$locale, TextType::class, [
                            'required' => true,
                            'label' => false,
                            'attr' => [
                                'rows' => 15,
                            ],
                            'property_path' => 'labels['.$locale.']',
                        ])
                    ;
                }
            })
        ;
        $methods = [
            'create' => 'POST',
            'update' => 'PUT',
            'position' => 'PATCH',
        ];
        $builder->setMethod($methods[$options['type']]);
        if($options['type'] === 'position') {
            $builder->add('position', NumberType::class, [
                'required' => false,
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => CampaignSortingFactorValue::class,
                'csrf_protection' => false,
            ])
            ->setRequired('type')
            ->setAllowedValues('type', ['create', 'update', 'position'])
        ;
    }

}
