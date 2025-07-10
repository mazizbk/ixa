<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-09-22 10:22:22
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextareaType;
use Azimut\Bundle\FrontofficeBundle\Entity\CmsFileText;

class CmsFileTextType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', I18nTextareaType::class, [
                'label' => false,
                'i18n_childen_options' => [
                    'attr' => [ 'rows' => '10' ],
                ]

            ])
        ;
    }

    public function setDefaultOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsFileText::class,
            'error_bubbling' => false
        ]);
    }
}
