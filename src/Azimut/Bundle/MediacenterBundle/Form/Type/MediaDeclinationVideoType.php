<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-10-31
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationImage;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaDeclinationVideoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isEmbeddedMedia', HiddenType::class, array(
                'mapped' => false,
            ))
            ->add('embedHtml', TextareaType::class, [
                'required' => false,
                'label' => 'embed.html.video'
            ])
            ->add('datetimeOriginal', null, [
                'label' => 'datetime.original'
            ])
            ->add('author', null, [
                'label' => 'author'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => MediaDeclinationImage::class,
            'error_bubbling' => false
        ));
    }
}
