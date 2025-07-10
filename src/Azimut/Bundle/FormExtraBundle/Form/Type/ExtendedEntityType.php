<?php
/**
 * Created by mikaelp on 30-Aug-18 11:01 AM
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;


use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Adds an option "choice_label_attr" to EntityType enabling every choice's label's attributes to be set by a callable
 */
class ExtendedEntityType extends AbstractType
{
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        parent::finishView($view, $form, $options);
        if(!is_callable($options['choice_label_attr'])) {
            return;
        }
        foreach ($view as $i => $choice) {
            $choice->vars['label_attr'] = $options['choice_label_attr']($view->vars['choices'][$i]->data);
            $choice->vars['block_prefixes'][] = $this->getBlockPrefix().'_entry';
        }
    }


    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('choice_label_attr')
            ->setAllowedTypes('choice_label_attr', 'callable')
        ;
    }

    public function getParent()
    {
        return EntityType::class;
    }

}
