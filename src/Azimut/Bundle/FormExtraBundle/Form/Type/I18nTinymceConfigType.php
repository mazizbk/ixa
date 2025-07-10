<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-02 16:46:44
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

class I18nTinymceConfigType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'i18n_form_type' => TinymceConfigType::class
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
