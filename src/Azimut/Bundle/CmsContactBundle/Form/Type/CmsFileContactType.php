<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-10 16:16:26
 */

namespace Azimut\Bundle\CmsContactBundle\Form\Type;

use Azimut\Bundle\CmsContactBundle\Entity\CmsFileContact;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CmsFileContactType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('firstName', null, array(
                'label' => 'first.name'
            ))
            ->add('lastName', null, array(
                'label' => 'last.name'
            ))
            ->add('address', TextareaType::class, array(
                'label' => 'address'
            ))
            ->add('zipCode', null, array(
                'label' => 'zip.code'
            ))
            ->add('city', null, array(
                'label' => 'city'
            ))
            ->add('country', null, array(
                'label' => 'country'
            ))
            ->add('phone', null, array(
                'label' => 'phone'
            ))
            ->add('email', null, array(
                'label' => 'email'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CmsFileContact::class,
            'error_bubbling' => false
        ));
    }
}
