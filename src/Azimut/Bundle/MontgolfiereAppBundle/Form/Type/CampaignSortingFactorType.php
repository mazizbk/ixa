<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactor;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignSortingFactorType extends AbstractType implements HasTypeOption
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                if(!$data || !$data instanceof CampaignSortingFactor) {
                    return;
                }
                $campaign = $data->getCampaign();

                foreach ($campaign->getAllowedLanguages() as $locale) {
                    $form
                        ->add('name_'.$locale, TextType::class, [
                            'required' => true,
                            'label' => false,
                            'attr' => [
                                'rows' => 15,
                            ],
                            'property_path' => 'names['.$locale.']',
                        ])
                    ;
                }
            })
        ;
        $builder->setMethod($options['type']==='create'?'POST':'PUT');
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => CampaignSortingFactor::class,
                'csrf_protection' => false,
            ])
            ->setDefined('type')
            ->setAllowedValues('type', ['create', 'update'])
        ;
    }

}
