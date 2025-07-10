<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-09-18 10:02:11
 */

namespace Azimut\Bundle\SecurityBundle\Form\Type;

use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\SecurityBundle\Entity\AccessRole;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AccessRightObjectType extends AbstractType
{
    public function __construct($roleProvider)
    {
        $this->roleProvider = $roleProvider;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('objectId', NumberType::class, array(
                'mapped' => false
            ))
            ->add('roles', EntityType::class, array(
                'class' => AccessRole::class,
              //  'choices' => $choices,
                'choice_label' => 'role',
                'multiple' => true,
                'expanded' => true
            ));
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => AccessRight::class,
            'object_class' => null,
            'objectId' => null,
        ));
    }
}
