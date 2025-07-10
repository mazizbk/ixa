<?php

namespace Azimut\Bundle\FormExtraBundle\Form\TypeExtension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class ExtraDataTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventListener(FormEvents::PRE_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $form = $event->getForm();

            if (!is_array($data)) {
                return;
            }

            $parent = $form;
            $allow  = false;
            for (; $parent; $parent = $parent->getParent()) {
                if ($parent->getConfig()->getOption('allow_form_extra_data')) {
                    $allow = true;
                    break;
                }
            }

            // nothing to do here
            if (!$allow) {
                return;
            }

            foreach ($data as $key => $value) {
                if (!$form->has($key)) {
                    unset($data[$key]);
                }
            }

            $event->setData($data);
        }, -1024);
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'allow_form_extra_data' => false,
        ));
    }
}
