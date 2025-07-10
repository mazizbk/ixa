<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-10-04
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;
use Azimut\Bundle\MediacenterBundle\Entity\MediaVideo;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaVideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('caption', I18nTextType::class, array(
                'label' => 'caption',
                'error_bubbling' => false,
            ))
            ->add('copyright', null, array(
                'label' => 'copyright'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => MediaVideo::class,
            'error_bubbling' => false
        ));
    }
}
