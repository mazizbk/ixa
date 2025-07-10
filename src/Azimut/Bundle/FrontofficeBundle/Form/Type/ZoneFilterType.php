<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-01 11:41:53
 */

namespace Azimut\Bundle\FrontofficeBundle\Form\Type;

use Azimut\Bundle\FrontofficeBundle\Entity\ZoneFilter;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class ZoneFilterType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('property', null, [
                'label' => 'property',
            ])
            ->add('operation', ChoiceType::class, array(
                'choices' => ZoneFilter::buildOperationsChoices(),
                'label'   => 'operation',
            ))
            ->add('name', null, [
                'label' => 'name (in query string)',
            ])
            ->add('label', null, [
                'label' => 'label (in filter form)',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
            'data_class' => ZoneFilter::class
        ]);
    }
}
