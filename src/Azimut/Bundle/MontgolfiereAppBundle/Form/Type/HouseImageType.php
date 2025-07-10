<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Model\HouseImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HouseImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('position', ChoiceType::class, [
                'choices' => [
                    'left' => HouseImage::POSITION_LEFT,
                    'top' => HouseImage::POSITION_TOP,
                ],
                'choice_translation_domain' => false,
            ])
            ->add('offset', HousePositionType::class, [
                'attr' => [
                    'class' => 'well',
                ]
            ])
            ->add('path', TextType::class, [
                'required' => false,
            ])
            ->add('scale', NumberType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HouseImage::class,
        ]);
    }

}
