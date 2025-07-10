<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignAnalysisGroup;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignAnalysisGroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'montgolfiere.backoffice.campaigns.analysis_groups.name',
            ])
            ->add('criteria', FilterCampaignParticipationsType::class, [
                'campaign' => $options['campaign'],
                'label' => false,
                'values-as-ids' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(['data_class' => CampaignAnalysisGroup::class,])
            ->setDefined('campaign')
            ->setAllowedTypes('campaign', [Campaign::class])
        ;
    }

}
