<?php


namespace Azimut\Bundle\FormExtraBundle\Form\TypeExtension;


use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

class HelpTextExtension extends AbstractTypeExtension
{
    private $options = [
        'help' => ['type' => 'string',],
        'help_translation_domain' => ['type' => ['string', 'bool',], 'default' => 'messages',],
        'help_raw' => ['type' => 'bool', 'default' => false,]
    ];

    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        foreach ($this->options as $optionKey => $optionValues) {
            if(array_key_exists($optionKey, $options)) {
                $view->vars[$optionKey] = $options[$optionKey];
            }
        }
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        foreach ($this->options as $key => $optionSettings) {
            $resolver->setDefined($key);
            $resolver->setAllowedTypes($key, $optionSettings['type']);
            if(array_key_exists('default', $optionSettings)) {
                $resolver->setDefault($key, $optionSettings['default']);
            }
        }
    }


    public function getExtendedType()
    {
        return FormType::class;
    }
}
