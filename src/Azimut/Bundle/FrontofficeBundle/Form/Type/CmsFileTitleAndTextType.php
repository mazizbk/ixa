<?php
/**
 * Created by mikaelp on 2018-10-17 5:27 PM
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;


use Azimut\Bundle\FormExtraBundle\Form\Type\I18nTextType;
use Azimut\Bundle\FrontofficeBundle\Entity\CmsFileTitleAndText;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class CmsFileTitleAndTextType extends AbstractType
{

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('title',I18nTextType::class, [
                'label' => 'title',
                'required' => true,
            ])
            ->add('text',I18nTextType::class, [
                'label' => 'text',
                'required' => true,
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => CmsFiletitleAndText::class,
            'error_bubbling' => false
        ));
    }

}
