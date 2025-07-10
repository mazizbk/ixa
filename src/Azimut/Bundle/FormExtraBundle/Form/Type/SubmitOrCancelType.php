<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-03-03 16:36:55
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\Form\SubmitButtonTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SubmitOrCancelType extends AbstractType implements SubmitButtonTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults(array(
                'label' => 'submit',
                'cancel_label' => 'cancel',
                'attr_cancel' => array()
            ))
            ->setAllowedTypes('cancel_label', ['string'])
            ->setAllowedTypes('attr_cancel', ['array'])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['cancel_label'] = $options['cancel_label'];
        $view->vars['attr_cancel'] = $options['attr_cancel'];
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return SubmitType::class;
    }
}
