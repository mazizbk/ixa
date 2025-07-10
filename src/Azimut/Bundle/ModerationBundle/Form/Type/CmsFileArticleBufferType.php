<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 16:50:16
 */

namespace Azimut\Bundle\ModerationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Azimut\Bundle\ModerationBundle\Entity\CmsFileArticleBuffer;
use Azimut\Bundle\FormExtraBundle\Form\Type\TinymceConfigType;

class CmsFileArticleBufferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'title'
            ])
            ->add('author', null, [
                'label' => 'author'
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
            'data_class' => CmsFileArticleBuffer::class,
            'error_bubbling' => false
        ]);
    }
}
