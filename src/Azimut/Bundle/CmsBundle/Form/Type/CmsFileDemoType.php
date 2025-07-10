<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-07-28
 */

namespace Azimut\Bundle\CmsBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;
use Azimut\Bundle\FormExtraBundle\Form\Type\TinymceConfigType;
use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTinymceConfigType;
use Azimut\Bundle\CmsBundle\Entity\CmsFileDemo;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Azimut\Bundle\CmsBundle\Entity\CmsFileArticle;

class CmsFileDemoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('myField', null, array(
                'label' => 'A non translatable field',
                'hint' => 'A demo field hint',
            ))
            ->add('myRadioField', ChoiceType::class, array(
                'label' => 'A single radio choice in array values',
                'choices' => array(
                    'Choice label A' => 'Choice value A',
                    'Choice label B' => 'Choice value B',
                    'Choice label C' => 'Choice value C',
                    'Choice label D' => 'Choice value D'
                ),
                'expanded' => true,
                'required' => false,
                'hint' => 'A demo field hint',
            ))
            ->add('myMultipleCheckboxesField', ChoiceType::class, array(
                'label' => 'A multiple checkboxes choices in array values',
                'choices' => array(
                    'Choice label A' => 'Choice value A',
                    'Choice label B' => 'Choice value B',
                    'Choice label C' => 'Choice value C',
                    'Choice label D' => 'Choice value D'
                ),
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'hint' => 'A demo field hint',
            ))
            ->add('mySelectField', ChoiceType::class, array(
                'label' => 'A single select choice in array values',
                'choices' => array(
                    'Choice label A' => 'Choice value A',
                    'Choice label B' => 'Choice value B',
                    'Choice label C' => 'Choice value C',
                    'Choice label D' => 'Choice value D'
                ),
                'required' => false,
                'hint' => 'A demo field hint',
            ))
            ->add('myMultipleSelectField', ChoiceType::class, array(
                'label' => 'A multiple select choices in array values',
                'choices' => array(
                    'Choice label A' => 'Choice value A',
                    'Choice label B' => 'Choice value B',
                    'Choice label C' => 'Choice value C',
                    'Choice label D' => 'Choice value D'
                ),
                'multiple' => true,
                'required' => false,
                'hint' => 'A demo field hint',
            ))
            ->add('myEntityRadioField', EntityType::class, array(
                'label' => 'A single radio choice in entity collection values',
                'class' => CmsFileArticle::class,
                'choice_label' => 'name',
                'expanded' => true,
                'required' => false,
                'hint' => 'A demo field hint',
            ))
            ->add('myEntityMultipleCheckboxesField', EntityType::class, [
                'label' => 'A multiple checkboxes choices in entity collection values',
                'class' => CmsFileArticle::class,
                'choice_label' => 'name',
                'multiple' => true,
                'expanded' => true,
                'required' => false,
                'hint' => 'A demo field hint',
            ])
            ->add('myEntitySelectField', EntityType::class, [
                'label' => 'A single select choice in entity collection values',
                'class' => CmsFileArticle::class,
                'hint' => 'A demo field hint',
            ])
            ->add('myEntityMultipleSelectField', EntityType::class, array(
                'label' => 'A multiple select choices entity collection values',
                'class' => CmsFileArticle::class,
                'choice_label' => 'name',
                'multiple' => true,
                'required' => false,
                'hint' => 'A demo field hint',
            ))
            //load tinymce config object only (as a data attribute)
            ->add('myRichTextField', TinymceConfigType::class, array(
                'attr' => array('rows' => '15'),
                'label' => 'A non translatable rich text field',
                'hint' => 'A demo field hint',
            ))
        ;
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CmsFileDemo::class,
            'error_bubbling' => false
        ));
    }
}
