<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-08-04
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationAnimation;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaDeclinationAnimationType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => MediaDeclinationAnimation::class,
            'error_bubbling' => false
        ));
    }
}
