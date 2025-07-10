<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-10-01 11:09:07
 */

namespace Azimut\Bundle\CmsBundle\Form\Type;

use Azimut\Bundle\CmsBundle\Entity\CmsFileProduct;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTinymceConfigType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CmsFileProductType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', I18nTextType::class, [
                'label' => 'title'
            ])
            ->add('subtitle', I18nTextType::class, [
                'label' => 'subtitle'
            ])
            //load tinymce config object only (as a data attribute)
            ->add('text', I18nTinymceConfigType::class, [
                'i18n_childen_options' => [
                    'attr' => ['rows' => '15']
                ],
                'label' => 'text'
            ])
            ->add('price', null, [
                'label' => 'price'
            ])
            ->add('associatedProducts', EntityType::class, [
                'label' => 'associated.products',
                'class' => CmsFileProduct::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsFileProduct::class,
            'error_bubbling' => false
        ]);
    }
}
