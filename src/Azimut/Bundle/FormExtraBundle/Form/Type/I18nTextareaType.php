<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-03-31 14:11:48
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class I18nTextareaType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'i18n_form_type' => TextareaType::class
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return I18nBaseType::class;
    }
}
