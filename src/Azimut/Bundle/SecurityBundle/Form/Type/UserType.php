<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-07 14:57:39
 */

namespace Azimut\Bundle\SecurityBundle\Form\Type;

use Azimut\Bundle\SecurityBundle\Entity\Group;
use Azimut\Bundle\SecurityBundle\Entity\User;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add(
                'username',
                EmailType::class,
                array(
                    'label' => 'email',
                    'disabled' => !$options['isnew'],
                    'attr' => [
                        'data-form-pre-icon' => 'glyphicon-pro-envelope',
                    ]
                )
            )
            ->add(
                'groups',
                EntityType::class,
                array(
                    'label' => 'groups',
                    'class' => Group::class,
                    'choice_label' => 'name',
                    'multiple' => true,
                    'expanded' => true,
                    'required' => false,
                    //'translation_count' => 10 // => PR https://github.com/symfony/symfony/pull/10125
                )
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(
            array(
                'data_class' => User::class,
                'isnew' => false,
            )
        );
    }
}
