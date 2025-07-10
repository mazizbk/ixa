<?php
/**
 * Created by mikaelp on 25-Jul-18 5:50 PM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Form\Type;


use Symfony\Component\Form\Extension\Core\Type\BaseType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ButtonsType extends BaseType
{
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);
        $resolver
            ->setDefault('mapped', false)
            ->setDefault('label', false)
            ->setDefault('centered', true)
            ->setDefault('required', false)
            ->setAllowedTypes('centered', 'boolean')
        ;
    }

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        parent::buildView($view, $form, $options);

        $view->vars['nested'] = true;
        $view->vars['centered'] = $options['centered'];
    }

    public function getBlockPrefix()
    {
        return 'buttons';
    }

}
