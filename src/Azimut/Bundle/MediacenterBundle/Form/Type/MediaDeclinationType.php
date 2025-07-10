<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-10-31
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Type\EntityHiddenType;
use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclination;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Azimut\Bundle\MediacenterBundle\Event\AzimutMediacenterEvents;
use Azimut\Bundle\MediacenterBundle\Event\FileUploadEvent;
use Symfony\Component\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\FormError;

class MediaDeclinationType extends AbstractType
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
            ->add('name', null, [
                'label' => 'name'
            ])
            ->add('type', HiddenType::class, [
                'mapped' => false
            ])
            ->add('isMainDeclination', CheckboxType::class, [
                'mapped' => false,
                'required' => false,
                'label' => 'main.declination'
            ])
        ;

        $doctrine = $this->registry;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($doctrine, $options) {
            $data = $event->getData();
            $form = $event->getForm();

            if (null === $data) {
                if (null === $options['media_declination_class']) {
                    //TODO: this exception is thrown by NelmioAPI doc ... find a way to configure it
                    //throw new \RuntimeException(sprintf('Expected media type option for type, or a data.'));
                    return;
                }

                if (null !== $options['media_declination_class']) {
                    $data = new $options['media_declination_class'];
                }
            }

            $event->setData($data);

            if (!$data instanceof MediaDeclination) {
                throw new \RuntimeException('Media declination form type only works with a MediaDeclination object');
            }

            $type = $data->getFormType();

            $mediaClass = str_replace('Declination', '', get_class($data));

            $form->add('file', null, [
                'label' => 'file',
                'required' => $data->isFileRequired(),
                'hint' => $mediaClass::FILE_TYPE_HINT_MESSAGE,
            ]);

            $form->add('mediaDeclinationType', $type, [
                'inherit_data' => true,
                'label' => false,
                'validation_groups' => $options['validation_groups']
            ]);

            if ($options['with_media_id']) {
                $form->add('media', EntityHiddenType::class, ['class' => Media::class]);
            }

            if ($options['hide_name']) {
                $form
                    ->remove('name')
                    ->add('name', HiddenType::class)
                    ->remove('isMainDeclination')
                    ->add('isMainDeclination', HiddenType::class, ['mapped' => false])
                ;
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['isMainDeclination']) && $data['isMainDeclination']) {
                $mediaDeclination = $form->getData();
                $mediaDeclination->getMedia()->setMainDeclination($mediaDeclination);
                $form->setData($mediaDeclination);
            }
        });

        //check disk usage and quota
        $eventDispatcher = $this->eventDispatcher;
        $builder->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) use ($eventDispatcher) {
            $data = $event->getData();
            $form = $event->getForm();

            $fileUploadEvent = new FileUploadEvent($data);
            $eventDispatcher->dispatch(
                AzimutMediacenterEvents::FILE_UPLOAD, $fileUploadEvent
            );

            if ($fileUploadEvent->isBlocked()) {
                $form->get('file')->addError(new FormError($fileUploadEvent->getBlockedMessage()));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => MediaDeclination::class,
            'media_declination_class' => null,
            'with_media_id' => false, // if set to true, activate field "media" (entity hidden) to explicitly provide the media id
            'hide_name' => false
        ]);
    }
}
