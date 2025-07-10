<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-10-02
 */

namespace Azimut\Bundle\MediacenterBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Type\EntityHiddenType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextareaType;
use Azimut\Bundle\MediacenterBundle\Entity\Folder;
use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MediaType extends AbstractType
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('locale', HiddenType::class, array(
                'mapped' => false
            ))
            ->add('name', TextType::class, array(
                'label' => 'name'
            ))
            ->add('description', I18nTextareaType::class, array(
                'label' => 'description'
            ))
            ->add('folder', EntityHiddenType::class, array(
                'class' => Folder::class,
                'by_reference' => false,
            ))
            //needed by the api to instanciate the media object
            ->add('type', HiddenType::class, array(
                'mapped' => false
            ))
            ->add('trashed', HiddenType::class)
        ;

        $doctrine = $this->registry;
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($doctrine, $options) {
            $data = $event->getData();
            $form = $event->getForm();

            if (null === $data) {
                return;
            }

            if (!$data instanceof Media) {
                throw new \RuntimeException('Media form type only works with a Media object');
            }

            $type = $data->getFormType();

            $form->add('mediaType', $type, array(
                'inherit_data' => true,
                'label' => false,
                'validation_groups' => $options['validation_groups']
            ));

            if ($options['with_one_declination']) {
                $form->add('mediaDeclinations', CollectionType::class, array(
                    'entry_type' => MediaDeclinationType::class,
                    'entry_options' => array(
                        'hide_name' => $options['hide_declination_name'],
                        'label' => false,
                        'error_bubbling' => false,
                        'validation_groups' => $options['validation_groups']
                    ),
                    'error_bubbling' => false,
                    'label' => false
                ));
            }

            if ($options['with_declinations']) {
                $form->add('mediaDeclinations', CollectionType::class, array(
                    'entry_type' => MediaDeclinationType::class,
                    'entry_options' => array(
                        'media_declination_class' => $data->getDeclinationClass(),
                        'validation_groups' => $options['validation_groups']
                    ),
                    'allow_add' => true,
                    'by_reference' => false,
                    'label' => false
                ));
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Media::class,
            'with_one_declination' => false,
            'with_declinations' => false,
            'hide_declination_name' => false
        ));
    }
}
