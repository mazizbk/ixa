<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-07-28
 */

namespace Azimut\Bundle\CmsBundle\Form\Type;

use Azimut\Bundle\CmsBundle\Entity\CmsFilePressReview;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTinymceConfigType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CmsFilePressReviewType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', I18nTextType::class, array(
                'label' => 'title'
            ))
            //load tinymce config object only (as a data attribute)
            ->add('text', I18nTinymceConfigType::class, array(
                'i18n_childen_options' => array(
                    'attr' => array('rows' => '15')
                ),
                'label' => 'text'
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CmsFilePressReview::class,
            'error_bubbling' => false
        ));
    }
}
