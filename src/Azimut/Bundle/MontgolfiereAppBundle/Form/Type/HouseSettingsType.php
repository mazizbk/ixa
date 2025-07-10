<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Model\HouseSettings;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HouseSettingsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('position', HousePositionType::class, [
                'attr' => [
                    'class' => 'well',
                ]
            ])
            ->add('dimension', HouseDimensionType::class, [
                'attr' => [
                    'class' => 'well',
                ]
            ])
        ;
        if(!$options['item']) {
            $builder
                ->add('isRoof', CheckboxType::class, [
                    'required' => false,
                ])
                ->add('image', HouseImageType::class, [
                    'attr' => [
                        'class' => 'well',
                    ]
                ])
                ->add('arrowDirection', ChoiceType::class, [
                    'choices' => [
                        'left' => HouseSettings::ARROW_DIRECTION_LEFT,
                        'down' => HouseSettings::ARROW_DIRECTION_DOWN,
                        'right' => HouseSettings::ARROW_DIRECTION_RIGHT,
                    ],
                    'choice_translation_domain' => false,
                ])
            ;
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => HouseSettings::class,
                'item' => false,
            ])
            ->setAllowedTypes('item', 'bool')
        ;
    }

}
