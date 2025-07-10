<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-06-23 14:28:16
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationOther;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;

class MediaDeclinationGenericEmbedHtmlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('isEmbeddedMedia', HiddenType::class, array(
                'mapped' => false,
                'required' => false
            ))
            ->add('embedHtml', TextareaType::class, [
                'required' => false,
                'label' => 'embed.html.video'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => MediaDeclinationOther::class,
            'error_bubbling' => false
        ));
    }
}
