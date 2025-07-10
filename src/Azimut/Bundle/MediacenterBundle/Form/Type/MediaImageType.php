<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-07-24
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;
use Azimut\Bundle\MediacenterBundle\Entity\MediaImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Azimut\Bundle\FormExtraBundle\Form\Type\GeolocationType;

class MediaImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('altText', I18nTextType::class, array(
                'label' => 'alternative.text',
                'error_bubbling' => false,
            ))
            ->add('caption', I18nTextType::class, array(
                'label' => 'caption',
                'error_bubbling' => false,
            ))
            ->add('copyright', null, array(
                'label' => 'copyright'
            ))
            ->add('geolocation', GeolocationType::class, array(
                'required' => false
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => MediaImage::class,
            'error_bubbling' => false
        ));
    }
}
