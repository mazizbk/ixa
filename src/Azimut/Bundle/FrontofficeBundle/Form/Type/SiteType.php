<?php

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SiteType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $editAllowed = !$options['disabled'];

        $builder
            ->add('name', TextType::class, [
                'label' => 'name'
            ])
            ->add('publisherName', TextType::class, [
                'label' => 'publisher.name'
            ])
        ;

        $builder
            ->add('title', I18nTextType::class, array(
                'label' => 'title'
            ))
            ->add('mainDomainName', DomainNameType::class, array(
                'label' => 'main.domain.name'
            ))
            ->add('scheme', ChoiceType::class, [
                'label'   => 'scheme',
                'choices' => [
                    'http'  => 'http',
                    'https' => 'https',
                ],
            ])
            ->add('secondaryDomainNames', CollectionType::class, array(
                'label' => 'secondary.domain.names',
                'entry_type' => DomainNameType::class,
                'allow_add' => $editAllowed,
                'allow_delete' => $editAllowed,
                'by_reference' => false, // disable replacement of collection (setDomainNames) and uses add/remove methods
                'entry_options' => array('label' => false),
                //'allow_form_extra_data' => true //because angularjs sends id in payload
                'required' => false
            ))
        ;
        if ($editAllowed) {
            $builder->add('layout', EntityType::class, array(
                'label' => 'layout',
                'class' => 'AzimutFrontofficeBundle:SiteLayout',
                'choice_label' => 'name',
            ));
        }
        $builder
            ->add('active', CheckboxType::class, array(
                'label' => 'site.active',
                'required' => false
            ))
            ->add('metaNoIndex', CheckboxType::class, array(
                'label' => 'site.meta.no.index',
                'required' => false
            ))
            ->add('commentsActive', CheckboxType::class, array(
                'label' => 'activate.comments',
                'required' => false
            ))
            ->add('commentModerationActive', CheckboxType::class, array(
                'label' => 'activate.comment.moderation',
                'required' => false
            ))
            ->add('commentRatingActive', CheckboxType::class, array(
                'label' => 'activate.comment.rating',
                'required' => false
            ))
            ->add('searchEngineActive', CheckboxType::class, array(
                'label' => 'activate.search.engine',
                'required' => false
            ))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Site::class,
            'translation_domain' => 'messages'
        ));
    }
}
