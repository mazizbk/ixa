<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-01-26 15:49:36
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Azimut\Bundle\FrontofficeBundle\Entity\PageLayout;
use Azimut\Bundle\FrontofficeBundle\Form\Type\ZoneDefinitionType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Burgov\Bundle\KeyValueFormBundle\Form\Type\KeyValueType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;

class PageLayoutType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'name',
            ])
            ->add('template', TextType::class, [
                'label' => 'template',
            ])
            ->add('standaloneRouterController', TextType::class, [
                'label'    => 'standalone router controller',
                'required' => false,
            ])
            ->add('standaloneRouterHasStandaloneCmsfilesRoutes', CheckboxType::class, [
                'label'    => 'standalone router has standalone CMS files routes',
                'required' => false,
                'hint'     => 'Does standalone router expose individual cmsfiles routes',

            ])
            ->add('zoneDefinitions', CollectionType::class, [
                'entry_type' => ZoneDefinitionType::class,
                'allow_add' => true,
                'allow_delete' => true,
                'by_reference' => false,
                'entry_options' => [
                    'label' => false,
                    'allow_extra_fields' => true
                ],
                'required' => false,
                'label' => 'zone definitions',
            ])
            ->add('templateOptions', KeyValueType::class, [
                'label'      => 'template options',
                'value_type' => TextType::class,
                'required'   => false,
                'hint'       => 'Retrieve page layout options from template in pageLayoutOptions (ex: pageLayoutOptions.myExampleOption)',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => PageLayout::class
        ]);
    }
}
