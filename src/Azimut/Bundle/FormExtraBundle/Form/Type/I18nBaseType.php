<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-04-02 17:33:35
 */

namespace Azimut\Bundle\FormExtraBundle\Form\Type;

use Azimut\Bundle\FormExtraBundle\Form\EventListener\I18nEventSubscriber;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class I18nBaseType extends AbstractType
{
    /**
     * @var string[]
     */
    private $availableLocales;

    public function __construct(array $availableLocales)
    {
        $this->availableLocales = $availableLocales;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->addEventSubscriber(new I18nEventSubscriber($options['i18n_form_type'], $this->availableLocales));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(array(
            'mapped'               => false,
            'i18n_form_type'       => TextType::class,
            'error_bubbling'       => false,
            'required'             => false,
            'i18n_childen_options' => array()
        ));
    }
}
