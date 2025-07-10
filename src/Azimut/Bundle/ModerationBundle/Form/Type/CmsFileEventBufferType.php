<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-12 17:19:46
 */

namespace Azimut\Bundle\ModerationBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Azimut\Bundle\ModerationBundle\Entity\CmsFileEventBuffer;
use Azimut\Bundle\FormExtraBundle\Form\Type\TinymceConfigType;
use Azimut\Bundle\FormExtraBundle\Form\Type\DateTimePickerType;

class CmsFileEventBufferType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', null, [
                'label' => 'title'
            ])
            ->add('eventStartDatetime', DateTimePickerType::class, [
                'label' => 'event.start.datetime'
            ])
            ->add('eventEndDatetime', DateTimePickerType::class, [
                'label' => 'event.end.datetime'
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
            'data_class' => CmsFileEventBuffer::class,
            'error_bubbling' => false
        ]);
    }
}
