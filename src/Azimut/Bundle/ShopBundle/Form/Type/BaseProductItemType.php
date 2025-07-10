<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-18 14:30:03
 */

namespace Azimut\Bundle\ShopBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\MoneyType;

use Azimut\Bundle\CmsBundle\Entity\BaseProductItem;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;

class BaseProductItemType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', I18nTextType::class, [
                'label' => 'name',
            ])
        ;

        if (true === $options['with_price']) {
            $builder->add('price', MoneyType::class, [
                'label'   => 'price',
                'divisor' => 100,
            ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => BaseProductItem::class,
            'with_price' => true,
        ]);
    }
}
