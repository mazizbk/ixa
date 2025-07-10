<?php
/**
 * Created by mikaelp on 05-Sep-18 10:05 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Client;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class FilterClientsType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', TextType::class, [
                'label' => 'montgolfiere.backoffice.clients.filter_form.name',
                'required' => false,
            ])
            ->add('type', ChoiceType::class, [
                'label' => 'montgolfiere.backoffice.clients.fields.client_type',
                'required' => false,
                'choices' => [
                    'montgolfiere.backoffice.clients.fields.client_type_values.client' => Client::STATUS_CLIENT,
                    'montgolfiere.backoffice.clients.fields.client_type_values.prospect' => Client::STATUS_PROSPECT,
                    'montgolfiere.backoffice.clients.fields.client_type_values.former_client' => Client::STATUS_FORMER_CLIENT,
                ],
            ])
            ->add('orderBy', ChoiceType::class, [
                'label' => 'montgolfiere.backoffice.clients.filter_form.order_by',
                'required' => true,
                'choices' => [
                    'montgolfiere.backoffice.clients.filter_form.name' => 'name',
                    'montgolfiere.backoffice.clients.fields.workforce' => 'workforce',
                    'montgolfiere.backoffice.clients.fields.turnover' => 'turnover',
                ]
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
                'display_all_route' => 'azimut_montgolfiere_app_backoffice_clients_homepage',
                'display_all_route_params' => [],
            ])
        ;
    }

    public function getBlockPrefix()
    {
        return 'filter';
    }


}
