<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Consultant;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterConsultantsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'montgolfiere.backoffice.consultants.filter_form.name',
                'required' => false,
            ])
            ->add('perpage', IntegerType::class, [
                'label' => 'montgolfiere.backoffice.common.filter_form.per_page',
                'required' => false,
                'data' => 100,
            ])
            ->add('buttons', ButtonsType::class)
        ;
        $buttons = $builder->get('buttons');
        $buttons
            ->add('submit', SubmitType::class, ['label' => 'montgolfiere.backoffice.common.filter_form.search', 'attr' => ['class' => 'btn-primary']])
            ->add('viewall', ButtonLinkType::class, [
                'route' => $options['display_all_route'],
                'route_params' => array_merge(['displayAll' => true], $options['display_all_route_params']),
                'text' => 'montgolfiere.backoffice.common.filter_form.show_all',
                'color' => 'default'
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'csrf_protection' => false,
                'method' => 'GET',
                'display_all_route' => 'azimut_montgolfiere_app_backoffice_consultants_homepage',
                'display_all_route_params' => [],
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'filter';
    }


}
