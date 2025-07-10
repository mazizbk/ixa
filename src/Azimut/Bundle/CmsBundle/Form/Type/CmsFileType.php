<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-06 11:35:52
 */

namespace Azimut\Bundle\CmsBundle\Form\Type;

use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileMainAttachmentTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileSecondaryAttachmentsTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment1Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment2Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment3Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment4Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileRelatedArticlesTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileSeoMetaTrait;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Azimut\Bundle\CmsBundle\Entity\CmsFileArticle;
use Azimut\Component\PHPExtra\TraitHelper;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextareaType;

class CmsFileType extends AbstractType
{
    private $registry;

    public function __construct(RegistryInterface $registry)
    {
        $this->registry = $registry;
    }
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
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

            if ($data && !$data instanceof CmsFile) {
                throw new \RuntimeException('CmsFile form type only works with a CmsFile object');
            }


            $type = $data->getFormType();

            $form->add('cmsFileType', $type, [
                'inherit_data' => true,
                'label' => false,
                'allow_extra_fields' => $options['allow_extra_fields']
            ]);

            // add main attachment only if current CmsFile subclass uses CmsFileMainAttachmentTrait
            if (TraitHelper::isClassUsing(get_class($data), CmsFileMainAttachmentTrait::class)) {
                $form->add('mainAttachment', CmsFileMediaDeclinationAttachmentType::class, [
                    'label'              => $data::getMainAttachmentLabel(),
                    'allow_extra_fields' => $options['allow_extra_fields'],
                    'required'           => false,
                    'by_reference'       => false,
                ]);
            }

            // Add complementary attachment 1 only if current CmsFile subclass uses CmsFileLogoAttachment1Trait
            if (TraitHelper::isClassUsing(get_class($data), CmsFileComplementaryAttachment1Trait::class)) {
                $form->add('complementaryAttachment1', CmsFileMediaDeclinationAttachmentType::class, [
                    'label'              => $data::getComplementaryAttachment1Label(),
                    'allow_extra_fields' => $options['allow_extra_fields'],
                    'required'           => false,
                    'by_reference'       => false,
                ]);
            }

            // Add complementary attachment 2 only if current CmsFile subclass uses CmsFileLogoAttachment2Trait
            if (TraitHelper::isClassUsing(get_class($data), CmsFileComplementaryAttachment2Trait::class)) {
                $form->add('complementaryAttachment2', CmsFileMediaDeclinationAttachmentType::class, [
                    'label'              => $data::getComplementaryAttachment2Label(),
                    'allow_extra_fields' => $options['allow_extra_fields'],
                    'required'           => false,
                    'by_reference'       => false,
                ]);
            }

            // Add complementary attachment 3 only if current CmsFile subclass uses CmsFileLogoAttachment3Trait
            if (TraitHelper::isClassUsing(get_class($data), CmsFileComplementaryAttachment3Trait::class)) {
                $form->add('complementaryAttachment3', CmsFileMediaDeclinationAttachmentType::class, [
                    'label'              => $data::getComplementaryAttachment3Label(),
                    'allow_extra_fields' => $options['allow_extra_fields'],
                    'required'           => false,
                    'by_reference'       => false,
                ]);
            }

            // Add complementary attachment 4 only if current CmsFile subclass uses CmsFileLogoAttachment4Trait
            if (TraitHelper::isClassUsing(get_class($data), CmsFileComplementaryAttachment4Trait::class)) {
                $form->add('complementaryAttachment4', CmsFileMediaDeclinationAttachmentType::class, [
                    'label'              => $data::getComplementaryAttachment4Label(),
                    'allow_extra_fields' => $options['allow_extra_fields'],
                    'required'           => false,
                    'by_reference'       => false,
                ]);
            }

            // add secondaryAttachments collection only if current CmsFile subclass uses CmsFileSecondaryAttachmentsTrait
            if (TraitHelper::isClassUsing(get_class($data), CmsFileSecondaryAttachmentsTrait::class)) {
                $form->add('secondaryAttachments', CollectionType::class, [
                    'entry_type' => CmsFileMediaDeclinationAttachmentType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'entry_options' => [
                        'label' => false,
                        'allow_extra_fields' => $options['allow_extra_fields']
                    ],
                    'required' => false,
                    'label' => $data::getSecondaryAttachmentsLabel(),
                    'allow_extra_fields' => $options['allow_extra_fields'],
                    'attr' => [
                        'prototype-orderby' => 'displayOrder',
                    ],
                ]);
            }

            // add relatedArticles collection only if current CmsFile subclass uses CmsFileRelatedArticlesTrait
            if (TraitHelper::isClassUsing(get_class($data), CmsFileRelatedArticlesTrait::class)) {
                $form->add('relatedArticles', CollectionType::class, [
                    'entry_type' => EntityType::class,
                    'allow_add' => true,
                    'allow_delete' => true,
                    'by_reference' => false,
                    'entry_options' => [
                        'label' => false,
                        'allow_extra_fields' => $options['allow_extra_fields'],
                        'class' => CmsFileArticle::class,
                    ],
                    'required' => false,
                    'label' => 'related.articles',
                    'allow_extra_fields' => $options['allow_extra_fields'],
                ]);
            }

            if (TraitHelper::isClassUsing(get_class($data), CmsFileSeoMetaTrait::class)) {
                $form
                    ->add('autoMetas', CheckboxType::class, [
                        'label' => 'automatic.metas',
                        'required' => false
                    ])
                    ->add('metaTitle', I18nTextType::class, array(
                        'label' => 'meta.title'
                    ))
                    ->add('metaDescription', I18nTextareaType::class, array(
                        'label' => 'meta.description'
                    ))
                ;
            }
        });

        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) use ($options) {
            $data = $event->getData();
            $form = $event->getForm();

            if (isset($data['cmsFileType']['publishStartDatetime']) && is_array(isset($data['cmsFileType']['publishStartDatetime']))) {
                if (!isset($data['cmsFileType']['publishStartDatetime']['time']['hour'])) {
                    $data['cmsFileType']['publishStartDatetime']['time']['hour'] = 0;
                }
                if (!isset($data['cmsFileType']['publishStartDatetime']['time']['minute'])) {
                    $data['cmsFileType']['publishStartDatetime']['time']['minute'] = 0;
                }
            }

            if (isset($data['cmsFileType']['publishEndDatetime']) && is_array($data['cmsFileType']['publishEndDatetime'])) {
                if (!isset($data['cmsFileType']['publishEndDatetime']['time']['hour'])) {
                    $data['cmsFileType']['publishEndDatetime']['time']['hour'] = 0;
                }
                if (!isset($data['cmsFileType']['publishEndDatetime']['time']['minute'])) {
                    $data['cmsFileType']['publishEndDatetime']['time']['minute'] = 0;
                }
            }

            // remove secondaryAttachments not containing a media declination
            if (isset($data['secondaryAttachments'])) {
                $secondaryAttachments = $data['secondaryAttachments'];

                foreach ($secondaryAttachments as $key => $attachment) {
                    if (empty($attachment['mediaDeclination']) || (is_array($attachment['mediaDeclination']) && count($attachment['mediaDeclination']) == 0)) {
                        unset($data['secondaryAttachments'][$key]);
                    }
                }
            }

            // Remove SEO meta data if not using trait (because JS layer is not guessing it)
            if (!TraitHelper::isClassUsing(get_class($form->getData()), CmsFileSeoMetaTrait::class)) {
                unset($data['autoMetas']);
                unset($data['metaTitle']);
                unset($data['metaDescription']);
            }

            $event->setData($data);
        });

        $builder->addEventListener(FormEvents::SUBMIT, function (FormEvent $event) {
            $data = $event->getData();

            if (TraitHelper::isClassUsing(get_class($data), CmsFileMainAttachmentTrait::class)) {
                    // remove mainAttachment if not containing a media declination
                    if (null != $data->getMainAttachment() && null === $data->getMainAttachment()->getMediaDeclination()) {
                        $data->setMainAttachment(null);
                    }
            }

            if (TraitHelper::isClassUsing(get_class($data), CmsFileComplementaryAttachment1Trait::class)) {
                // Remove complementary attachment 1 if not containing a media declination
                if (null != $data->getComplementaryAttachment1() && null === $data->getComplementaryAttachment1()->getMediaDeclination()) {
                    $data->setComplementaryAttachment1(null);
                }
            }

            if (TraitHelper::isClassUsing(get_class($data), CmsFileComplementaryAttachment2Trait::class)) {
                // Remove complementary attachment 2 if not containing a media declination
                if (null != $data->getComplementaryAttachment2() && null === $data->getComplementaryAttachment2()->getMediaDeclination()) {
                    $data->setComplementaryAttachment2(null);
                }
            }

            if (TraitHelper::isClassUsing(get_class($data), CmsFileComplementaryAttachment3Trait::class)) {
                // Remove complementary attachment 3 if not containing a media declination
                if (null != $data->getComplementaryAttachment3() && null === $data->getComplementaryAttachment3()->getMediaDeclination()) {
                    $data->setComplementaryAttachment3(null);
                }
            }

            if (TraitHelper::isClassUsing(get_class($data), CmsFileComplementaryAttachment4Trait::class)) {
                // Remove complementary attachment 4 if not containing a media declination
                if (null != $data->getComplementaryAttachment4() && null === $data->getComplementaryAttachment4()->getMediaDeclination()) {
                    $data->setComplementaryAttachment4(null);
                }
            }
        });
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CmsFile::class
        ));
    }
}
