<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-18 14:30:03
 */

namespace Azimut\Bundle\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Azimut\Bundle\ShopBundle\Form\Type\BaseProductItemType;
use Azimut\Bundle\CmsBundle\Entity\ProductItem;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\FormExtraBundle\Form\Type\EntityHiddenType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextareaType;

class ProductItemType extends AbstractType
{
    public function getParent()
    {
        return BaseProductItemType::class;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('text', I18nTextareaType::class, [
                'label' => 'text',
                'i18n_childen_options' => [
                    'attr' => ['rows' => '15']
                ],
            ])
        ;

        if (true === $options['with_hidden_cms_file']) {
            $builder->add('cmsFile', EntityHiddenType::class, [
                'class' => CmsFile::class
            ]);
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class'           => ProductItem::class,
            'with_hidden_cms_file' => false,
        ]);
    }
}
