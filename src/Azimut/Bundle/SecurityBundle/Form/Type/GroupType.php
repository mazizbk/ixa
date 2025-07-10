<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-09 14:57:39
 */

namespace Azimut\Bundle\SecurityBundle\Form\Type;

use Azimut\Bundle\SecurityBundle\Entity\Group;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class GroupType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', null, array(
                'label' => 'name'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Group::class
        ));
    }
}
