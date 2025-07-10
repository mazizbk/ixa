<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-01 10:58:47
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinition;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinitionCmsFiles;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinitionForm;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinitionCmsFileBufferForm;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;

class ZoneDefinitionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'name',
            ])
            ->add('zoneDefinitionType', ChoiceType::class, [
                'label' => 'type',
                'choices' => array(
                    'Simple' => ZoneDefinition::ZONE_DEFINITION_TYPE,
                    'Cms files' => ZoneDefinitionCmsFiles::ZONE_DEFINITION_TYPE,
                    'Form' => ZoneDefinitionForm::ZONE_DEFINITION_TYPE,
                    'Cms file buffer form' => ZoneDefinitionCmsFileBufferForm::ZONE_DEFINITION_TYPE,
                ),
                'mapped' => false,
                'attr' => [
                    'az-toggle-data-form-section' => 'class',
                ],
            ])

            // ZoneDefinitionCmsFiles type
            ->add('acceptedAttachmentClasses', CollectionType::class, [
                'allow_add' => true,
                'allow_delete' => true,
                'entry_options' => [
                    'label' => false,
                ],
                'required' => false,
                'label' => 'accepted attachment classes',
                'prototype_name' => '__accepted_attachment_classes_name__',
                'by_reference' => false,
                'attr' => [
                    'data-form-section' => 'class',
                    'data-form-section-value' => ZoneDefinitionCmsFiles::ZONE_DEFINITION_TYPE,
                ],
            ])
            ->add('maxAttachmentsCount', IntegerType::class, [
                'label' => 'max attachments count',
                'attr' => [
                    'data-form-section' => 'class',
                    'data-form-section-value' => ZoneDefinitionCmsFiles::ZONE_DEFINITION_TYPE,
                ],
            ])
            ->add('allowDeleteAttachments', CheckboxType::class, [
                'label' => 'allow delete attachments',
                'attr' => [
                    'data-form-section' => 'class',
                    'data-form-section-value' => ZoneDefinitionCmsFiles::ZONE_DEFINITION_TYPE,
                ],
            ])
            ->add('autoFillAttachments', CheckboxType::class, [
                'label' => 'auto fill attachments',
                'attr' => [
                    'data-form-section' => 'class',
                    'data-form-section-value' => ZoneDefinitionCmsFiles::ZONE_DEFINITION_TYPE,
                ],
            ])
            ->add('excludeUntranslatedCmsFiles', CheckboxType::class, [
                'label' => 'exlude untranslated cmsfiles',
                'attr' => [
                    'data-form-section' => 'class',
                    'data-form-section-value' => ZoneDefinitionCmsFiles::ZONE_DEFINITION_TYPE,
                ],
            ])
            ->add('cmsFilePathPriority', IntegerType::class, [
                'label' => 'Cmsfile path priority',
                'hint'  => 'Priority when processing the canonical path of a cmsfile'
            ])
            ->add('hasStandaloneCmsfilesRoutes', CheckboxType::class, [
                'label' => 'has standalone CMS files routes',
                'hint'  => 'Cmsfiles published in this zone will have there own URL relative to the page to display its content'
            ])
            ->add('filters', CollectionType::class, [
                'entry_type' => ZoneFilterType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_options' => [
                    'label' => false,
                    'allow_extra_fields' => true
                ],
                'required' => false,
                'label' => 'filters',
                'prototype_name' => '__filters_name__',
                'attr' => [
                    'data-form-section' => 'class',
                    'data-form-section-value' => ZoneDefinitionCmsFiles::ZONE_DEFINITION_TYPE,
                ],
            ])
            ->add('permanentFilters', CollectionType::class, [
                'entry_type' => ZonePermanentFilterType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_options' => [
                    'label' => false,
                    'allow_extra_fields' => true
                ],
                'required' => false,
                'label' => 'permanent filters',
                'prototype_name' => '__permanent_filters_name__',
                'attr' => [
                    'data-form-section' => 'class',
                    'data-form-section-value' => ZoneDefinitionCmsFiles::ZONE_DEFINITION_TYPE,
                ],
            ])

            // ZoneDefinitionForm type
            ->add('controller', TextType::class, [
                'label' => 'controller',
                'attr' => [
                    'data-form-section' => 'class',
                    'data-form-section-value' => ZoneDefinitionForm::ZONE_DEFINITION_TYPE,
                ],
            ])

            // ZoneDefinitionCmsFileBufferForm type
            ->add('cmsFileBufferClass', TextType::class, [
                'label' => 'form type',
                'attr' => [
                    'data-form-section' => 'class',
                    'data-form-section-value' => ZoneDefinitionCmsFileBufferForm::ZONE_DEFINITION_TYPE,
                ],
            ])
            ->add('targetZone', EntityZoneTreeType::class, [
                'label' => 'target zone',
                'attr' => [
                    'data-form-section' => 'class',
                    'data-form-section-value' => ZoneDefinitionCmsFileBufferForm::ZONE_DEFINITION_TYPE,
                ],
            ])
        ;

        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $form = $event->getForm();

            if (null !== $data) {
                if (!($data instanceof ZoneDefinitionCmsFiles)) {
                    $form
                        ->remove('acceptedAttachmentClasses')
                        ->remove('maxAttachmentsCount')
                        ->remove('allowDeleteAttachments')
                        ->remove('autoFillAttachments')
                        ->remove('excludeUntranslatedCmsFiles')
                        ->remove('filters')
                        ->remove('permanentFilters')
                        ->remove('cmsFilePathPriority')
                        ->remove('hasStandaloneCmsfilesRoutes')
                    ;
                }

                if (!($data instanceof ZoneDefinitionForm)) {
                    $form
                        ->remove('formType')
                        ->remove('controller')
                    ;
                }

                if (!($data instanceof ZoneDefinitionCmsFileBufferForm)) {
                    $form
                        ->remove('cmsFileBufferClass')
                        ->remove('targetZone')
                    ;
                }
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $form = $event->getForm();

            if (null == $form->getData()) {
                if ('cms_files' == $data['zoneDefinitionType']) {
                    $form->setData(new ZoneDefinitionCmsFiles());
                }
                elseif ('form' == $data['zoneDefinitionType']) {
                    $form->setData(new ZoneDefinitionForm());
                }
                elseif ('cms_file_buffer_form' == $data['zoneDefinitionType']) {
                    $form->setData(new ZoneDefinitionCmsFileBufferForm());
                }
                else {
                    throw new \Exception(sprintf('Unknown zone type "%s"', $data['zoneDefinitionType']));
                }
            }

            if ('cms_files' != $data['zoneDefinitionType']) {
                $form
                    ->remove('acceptedAttachmentClasses')
                    ->remove('maxAttachmentsCount')
                    ->remove('allowDeleteAttachments')
                    ->remove('autoFillAttachments')
                    ->remove('excludeUntranslatedCmsFiles')
                    ->remove('filters')
                    ->remove('permanentFilters')
                    ->remove('cmsFilePathPriority')
                ;
            }

            if ('form' != $data['zoneDefinitionType']) {
                $form
                    ->remove('formType')
                    ->remove('controller')
                ;
            }

            if ('cms_file_buffer_form' != $data['zoneDefinitionType']) {
                $form
                    ->remove('cmsFileBufferClass')
                    ->remove('targetZone')
                ;
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ZoneDefinition::class,
        ]);
    }
}
