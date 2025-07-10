<?php
/**
 * Created by mikaelp on 05-Sep-18 10:05 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterCampaignsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'montgolfiere.backoffice.campaigns.fields.name',
                'required' => false,
                'attr' => [
                    'autocomplete' => 'off',
                ]
            ])
            ->add('showExpired', CheckboxType::class, [
                'label' => 'montgolfiere.backoffice.campaigns.list.display_expired_campaigns',
                'required' => false,
            ])
            ->add('showUpcoming', CheckboxType::class, [
                'label' => 'montgolfiere.backoffice.campaigns.list.display_upcoming_campaigns',
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
                'route' => 'azimut_montgolfiere_app_backoffice_campaigns_homepage',
                'route_params' => ['displayAll' => true],
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
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'filter';
    }

}
