<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-24 16:00:00
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\MediacenterBundle\Entity\MediaText;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaTextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('author', null, array(
                'label' => 'author'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => MediaText::class,
            'error_bubbling' => false
        ));
    }
}
