<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-16 17:13:07
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Azimut\Bundle\MediacenterBundle\Entity\MediaGenericEmbedHtml;

class MediaGenericEmbedHtmlType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {

    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => MediaGenericEmbedHtml::class,
            'error_bubbling' => false
        ));
    }
}
