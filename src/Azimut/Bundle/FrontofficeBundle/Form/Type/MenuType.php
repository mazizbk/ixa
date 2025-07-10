<?php

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Azimut\Bundle\FormExtraBundle\Form\Type\EntityHiddenType;
use Azimut\Bundle\FrontofficeBundle\Entity\Menu;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('site', EntityHiddenType::class, array('class' => Site::class))
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => Menu::class,
            'translation_domain' => 'messages'
        ));
    }
}
