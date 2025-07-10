<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-10-31
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Azimut\Bundle\FormExtraBundle\Form\Type\DateTimePickerType;

class MediaDeclinationImageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('author', null, [
                'label' => 'author'
            ])
            ->add('pixelWidth', null, [
                'label' => 'pixel.width'
            ])
            ->add('pixelHeight', null, [
                'label' => 'pixel.height'
            ])
            ->add('datetimeOriginal', DateTimePickerType::class, [
                'label' => 'datetime.original',
                'required' => false,
            ])
            ->add('software', null, [
                'label' => 'software'
            ])
            ->add('deviceMaker', null, [
                'label' => 'device.maker'
            ])
            ->add('deviceModel', null, [
                'label' => 'device.model'
            ])
            ->add('orientation', ChoiceType::class, [
                'label' => 'image.orientation',
                'choices' => [
                    'landscape' => 1,
                    'landscape.flipped' => 2,
                    'landscape.reverse' => 3,
                    'landscape.reverse.flipped' => 4,
                    'portrait.flipped' => 5,
                    'portrait.reverse' => 6,
                    'portrait.reverse.flipped' => 7,
                    'portrait' => 8
                ],
                'required' => false
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MediaDeclinationImage::class,
            'error_bubbling' => false
        ]);
    }
}
