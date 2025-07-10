<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-06-03 15:41:28
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class I18nUrlType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'i18n_form_type' => UrlType::class
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
