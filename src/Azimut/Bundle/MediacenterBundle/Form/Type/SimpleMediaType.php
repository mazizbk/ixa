<?php

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Type\EntityHiddenType;
use Azimut\Bundle\MediacenterBundle\Entity\Folder;
use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Azimut\Bundle\MediacenterBundle\Event\AzimutMediacenterEvents;
use Azimut\Bundle\MediacenterBundle\Event\FileUploadEvent;
use Symfony\Component\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;

class SimpleMediaType extends AbstractType
{
    private $registry;
    private $eventDispatcher;

    public function __construct(RegistryInterface $registry, EventDispatcherInterface $eventDispatcher)
    {
        $this->registry = $registry;
        $this->eventDispatcher = $eventDispatcher;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('upload', MediaFromFileType::class, array(
                'mapped' => false,
                'error_bubbling' => true,
                'label' => false,
            ))
            ->add('folder', EntityHiddenType::class, array('class' => Folder::class))
        ;

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $form = $event->getForm();

            $media = $form->get('upload')->getData();

            if ($media) {
                $media->setFolder($form->get('folder')->getData());

                $event->setData($media);
            }
        });

        //check disk usage and quota
        $eventDispatcher = $this->eventDispatcher;
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($eventDispatcher) {
            $data = $event->getData();
            $form = $event->getForm();

            if ($data->getMainDeclination()) {
                $fileUploadEvent = new FileUploadEvent($data->getMainDeclination());
                $eventDispatcher->dispatch(
                    AzimutMediacenterEvents::FILE_UPLOAD, $fileUploadEvent
                );

                if ($fileUploadEvent->isBlocked()) {
                    $form->get('upload')->addError(new FormError($fileUploadEvent->getBlockedMessage()));
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Media::class
        ));
    }
}
