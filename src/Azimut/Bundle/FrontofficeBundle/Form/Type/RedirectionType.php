<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-07-01 11:36:32
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RedirectionType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('address', null, array(
                'label' => $options['subfields_label']? 'address' : false
            ))
        ;


        if ($options['include_page_field']) {
            $builder->add('page', null, array(
                'label' => 'page'
            ));
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'data_class' => 'Azimut\Bundle\FrontofficeBundle\Entity\Redirection',
            'include_page_field' => true,
            'subfields_label' => true,
        ));
    }
}
