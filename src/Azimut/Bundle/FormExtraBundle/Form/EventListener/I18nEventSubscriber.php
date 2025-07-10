<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-03-31 14:26:18
 */

namespace Azimut\Bundle\FormExtraBundle\Form\EventListener;

use Azimut\Bundle\DoctrineExtraBundle\Entity\TranslatableEntityInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

class I18nEventSubscriber implements EventSubscriberInterface
{
    private $availableLocales = array();
    private $formType;

    public function __construct($formType, array $availableLocales)
    {
        $this->availableLocales = $availableLocales;
        $this->formType         = $formType;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'onPreSetData',
            FormEvents::SUBMIT       => 'onSubmit',
        );
    }

    public function onPreSetData(FormEvent $event)
    {
        $form   = $event->getForm();
        $parent = $form->getParent();
        if ($parent) {
            $data   = $parent->getData();
            $name   = $form->getName();
            $form_options = $form->getConfig()->getOptions();

            $form_children_options = array();
            if (isset($form_options['i18n_childen_options'])) {
                $form_children_options = $form_options['i18n_childen_options'];
            }

            foreach ($this->getLocaleData($data, $name) as $locale => $text) {
                if (!isset($form_children_options['attr'])) {
                    $form_children_options['attr'] = array();
                }

                $form->add($locale, $this->formType, array_merge($form_children_options, array(
                    'data' => $text,
                    'label' => false,
                    'attr' => array_merge($form_children_options['attr'], array('data-form-i18n' => $locale))
                )));
            }
        }
    }

    public function onSubmit(FormEvent $event)
    {
        $form   = $event->getForm();
        $parent = $form->getParent();
        $data   = $parent->getData();
        $name   = $form->getName();

        foreach ($event->getData() as $key => $value) {
            $method = 'set'.str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
            $data->$method($value, $key);
        }
    }

    /**
     * @param TranslatableEntityInterface $object
     * @param string $name
     * @return array
     */
    private function getLocaleData($object, $name)
    {
        if (!is_object($object)) {
            return array_fill_keys($this->availableLocales, null);
        }

        $collection = $object->getTranslations();
        $method = 'get'.str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
        $locales = array();
        foreach ($this->availableLocales as $locale) {
            if (!isset($locales[$locale])) {
                $locales[$locale] = $object->$method($locale);
            }
        }
        if (null != $collection) {
            foreach ($collection as $translation) {
                $locale = $translation->getLocale();
                if (!isset($locales[$locale])) {
                    $locales[$locale] = $object->$method($locale);
                }
            }
        }

        return $locales;
    }
}
