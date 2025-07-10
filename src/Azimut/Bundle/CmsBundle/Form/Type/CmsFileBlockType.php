<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-06 11:45:42
 */

namespace Azimut\Bundle\CmsBundle\Form\Type;

use Azimut\Bundle\CmsBundle\Entity\CmsFileBlock;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTinymceConfigType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Azimut\Bundle\SecurityBundle\Entity\Group;
use Azimut\Bundle\FormExtraBundle\Form\Type\DateTimePickerType;

class CmsFileBlockType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', I18nTextType::class, [
                'label' => 'title',
            ])
            ->add('text', I18nTinymceConfigType::class, [
                'i18n_childen_options' => [
                    'attr' => ['rows' => '15']
                ],
                'label' => 'text',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsFileBlock::class,
            'error_bubbling' => false,
        ]);
    }
}
