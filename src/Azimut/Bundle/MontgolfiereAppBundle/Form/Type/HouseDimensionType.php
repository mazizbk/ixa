<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Model\HouseDimension;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HouseDimensionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('width', IntegerType::class, [
                'required' => false,
            ])
            ->add('height', IntegerType::class, [
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => HouseDimension::class
        ]);
    }

}
