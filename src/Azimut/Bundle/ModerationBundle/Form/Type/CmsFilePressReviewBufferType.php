<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-12 17:22:11
 */

namespace Azimut\Bundle\ModerationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Azimut\Bundle\ModerationBundle\Entity\CmsFilePressReviewBuffer;
use Azimut\Bundle\FormExtraBundle\Form\Type\TinymceConfigType;

class CmsFilePressReviewBufferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'title'
            ])
            ->add('text', TinymceConfigType::class, [
                'attr' => ['rows' => '15'],
                'label' => 'text'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsFilePressReviewBuffer::class,
            'error_bubbling' => false
        ]);
    }
}
