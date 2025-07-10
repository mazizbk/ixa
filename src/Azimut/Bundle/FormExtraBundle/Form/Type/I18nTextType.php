<?php

namespace Azimut\Bundle\FormExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class I18nTextType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'i18n_form_type' => TextType::class
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
