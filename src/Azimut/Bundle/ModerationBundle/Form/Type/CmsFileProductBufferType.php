<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-12 09:56:55
 */

namespace Azimut\Bundle\ModerationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Azimut\Bundle\ModerationBundle\Entity\CmsFileProductBuffer;
use Azimut\Bundle\FormExtraBundle\Form\Type\TinymceConfigType;

class CmsFileProductBufferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'title'
            ])
            ->add('subtitle', null, [
                'label' => 'subtitle'
            ])
            ->add('price', null, [
                'label' => 'price'
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
            'data_class' => CmsFileProductBuffer::class,
            'error_bubbling' => false
        ]);
    }
}
