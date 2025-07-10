<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-17 16:38:39
 */

namespace Azimut\Bundle\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Azimut\Bundle\CmsBundle\Entity\CmsFileEvent;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTinymceConfigType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Azimut\Bundle\FormExtraBundle\Form\Type\DateTimePickerType;

class CmsFileEventType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title', I18nTextType::class, [
                'label' => 'title',
            ])
            ->add('location', TextType::class, [
                'label'    => 'location',
                'required' => false
            ])
            ->add('eventStartDatetime', DateTimePickerType::class, [
                'label' => 'event.start.datetime',
                'years' => range(2000, date('Y') + 5),
            ])
            ->add('eventEndDatetime', DateTimePickerType::class, [
                'label' => 'event.end.datetime',
                'years' => range(2000, date('Y') + 5),
            ])
            ->add('publishStartDatetime', DateTimePickerType::class, [
                'label' => 'publish.start.datetime',
                'years' => range(2000, date('Y') + 5),
            ])
            ->add('publishEndDatetime', DateTimePickerType::class, [
                'label' => 'publish.end.datetime',
                'years' => range(2000, date('Y') + 5),
            ])
            ->add('text', I18nTinymceConfigType::class, [
                'i18n_childen_options' => [
                    'attr' => ['rows' => '15'],
                ],
                'label' => 'text',
            ])
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => CmsFileEvent::class,
            'error_bubbling' => false,
        ]);
    }
}
