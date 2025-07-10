<?php
/**
 * Created by mikaelp on 05-Sep-18 2:33 PM
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;


use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class BootstrapDatePickerType extends DateType
{
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);
        if(array_key_exists('format', $options)) {
            $view->vars['format'] = $options['format'];
        }
        if(array_key_exists('todayHighlight', $options)) {
            $view->vars['todayHighlight'] = $options['todayHighlight'];
        }
        $view->vars['autoclose'] = $options['autoclose'];
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver
            ->setDefined('todayHighlight')
            ->setDefault('autoclose', true)
            ->setDefault('widget', 'single_text')
            ->setDefault('format', 'dd/MM/yyyy')
            ->setAllowedTypes('autoclose', 'boolean')
            ->setAllowedTypes('todayHighlight', 'boolean')
        ;
    }

    public function getBlockPrefix()
    {
        return 'datepicker';
    }

}
