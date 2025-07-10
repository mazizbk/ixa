<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-10-25 16:54:09
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Type\EntityHiddenType;
use Azimut\Bundle\MediacenterBundle\Entity\Folder;
use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\FormError;

class EmbedHtmlMediaType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('embed', MediaFromEmbedHtmlType::class, [
                'mapped' => false,
                'error_bubbling' => true,
                'label' => false
            ])
            ->add('folder', EntityHiddenType::class, ['class' => Folder::class])
        ;

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();
            $embed = $event->getData()['embed'];

            if (1 != preg_match('/<iframe[^>]+>[^<]*<\/iframe>/', $embed)) {
                $form->addError(new FormError('embed.html.must.contain.iframe'));
            }
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            $media = $form->get('embed')->getData();

            if ($media) {
                $media->setFolder($form->get('folder')->getData());

                $event->setData($media);
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Media::class
        ]);
    }
}
