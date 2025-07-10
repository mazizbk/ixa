<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\FormExtraBundle\Form\Type\EntityHiddenType;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSegmentStep;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Item;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Theme;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CampaignSegmentStepType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('position', IntegerType::class)
            ->add('type', ChoiceType::class, [
                'choices' => [
                    'divider' => CampaignSegmentStep::TYPE_DIVIDER,
                    'item' => CampaignSegmentStep::TYPE_ITEM,
                    'question' => CampaignSegmentStep::TYPE_QUESTION,
                ],
            ])
            ->add('theme', EntityHiddenType::class, [
                'class' => Theme::class,
            ])
            ->add('item', EntityHiddenType::class, [
                'class' => Item::class,
            ])
            ->add('question', EntityHiddenType::class, [
                'class' => Question::class,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CampaignSegmentStep::class,
            'csrf_protection' => false,
        ]);
    }

    public function getBlockPrefix()
    {
        return '';
    }


}
