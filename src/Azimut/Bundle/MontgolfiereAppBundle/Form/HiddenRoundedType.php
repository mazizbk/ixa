<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\FormBuilderInterface;

class HiddenRoundedType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addModelTransformer(new CallbackTransformer(function($value){
            return $value;
        }, function($value){
            return round($value);
        }));
    }

    public function getParent()
    {
        return HiddenType::class;
    }


}
