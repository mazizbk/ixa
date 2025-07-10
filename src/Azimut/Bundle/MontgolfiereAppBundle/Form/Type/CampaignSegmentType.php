<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegment;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignSegmentType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'required' => true,
                'label' => 'montgolfiere.backoffice.campaigns.segments.name',
            ])
            ->add('disabled', CheckboxType::class, [
                'label' => 'montgolfiere.backoffice.campaigns.segments.disabled',
                'required' => false,
            ])
            ->addEventListener(FormEvents::PRE_SET_DATA, function(FormEvent $event) {
                $form = $event->getForm();
                $data = $event->getData();
                if(!$data || !$data instanceof CampaignSegment || !$data->getCampaign()) {
                    return;
                }

                $locales = [];
                foreach ($data->getCampaign()->getAllowedLanguages() as $allowedLanguage) {
                    $locales['montgolfiere.backoffice.campaigns.locale.'.$allowedLanguage] = $allowedLanguage;
                }

                $form
                    ->add('locale', ChoiceType::class, [
                        'label' => 'montgolfiere.backoffice.campaigns.segments.locale',
                        'required' => true,
                        'choices' => $locales,
                        'multiple' => false,
                        'expanded' => false,
                    ])
                ;
            })
        ;
    }/**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CampaignSegment::class
        ));
    }

    public function getBlockPrefix()
    {
        return '';
    }


}
