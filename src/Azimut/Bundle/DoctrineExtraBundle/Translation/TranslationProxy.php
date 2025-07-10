<?php
/**
 * @author: Alexandre Salomé
 * date:   2014-01-17
 */

namespace Azimut\Bundle\DoctrineExtraBundle\Translation;

use Azimut\Bundle\DoctrineExtraBundle\Entity\EntityTranslationInterface;
use Azimut\Bundle\DoctrineExtraBundle\Entity\TranslatableEntityInterface;

class TranslationProxy
{
    private $entity;
    private $locale;
    private static $defaultLocale = null;

    public function __construct(TranslatableEntityInterface $entity, $locale = null)
    {
        $this->entity = $entity;
        $this->locale = $locale;
    }

    public function __call($method, $arguments)
    {
        $locale = $this->getLocale();

        if ($locale == "all") {
            $translations = $this->getAllTranslations($method, $arguments);
            $translations_return = array();

            foreach ($translations as $translation_locale => $translation) {
                if (!method_exists($translation, $method)) {
                    throw new \RuntimeException(sprintf('Call to undefined method "%s" on entity "%s".', $method, get_class($translation)));
                }

                $translations_return[$translation_locale] = call_user_func_array(array($translation, $method), $arguments);
            }

            // return null instead of empty array (because JSON serializer would return [] instead of {})
            if (0 == count($translations_return)) {
                $translations_return = null;
            }

            return $translations_return;
        }

        $translation = $this->getTranslation($method, $arguments);
        if (!method_exists($translation, $method)) {
            throw new \RuntimeException(sprintf('Call to undefined method "%s" on entity "%s".', $method, get_class($translation)));
        }

        $value = call_user_func_array(array($translation, $method), $arguments);

        return $value;
    }

    private function getLocale()
    {
        if (null === $this->locale) {
            $locale = self::getDefaultLocale();

            if (false !== $pos = strpos($locale, '_')) {
                $locale = substr($locale, 0, $pos);
            }
        } else {
            $locale = $this->locale;
        }

        return $locale;
    }

    public static function setDefaultLocale($defaultLocale)
    {
        self::$defaultLocale = $defaultLocale;
    }

    public static function getDefaultLocale()
    {
        if (null === self::$defaultLocale) {
            return \Locale::getDefault();
        }

        return self::$defaultLocale;
    }

    private function getTranslation($method, array $arguments, $locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        $translations = $this->entity->getTranslations();

        if (isset($translations[$locale])) {
            return $translations[$locale];
        }

        /** @var TranslatableEntityInterface $className */
        $className = get_class($this->entity);
        $translationClassName = $className::getTranslationClass();
        /** @var EntityTranslationInterface $translation */
        $translation = new $translationClassName();
        $translation->setLocale($locale);
        $translation->setTranslatable($this->entity);

        // Setter without argument or with null value should be ignored otherwise empty translations will be added to current entity.
        if (0 === strpos($method, 'set') && (!empty($arguments) && $arguments[0])) {
            $translations[$locale] = $translation;
        }

        return $translation;
    }

    private function getAllTranslations($method, $arguments)
    {
        $translations = $this->entity->getTranslations();

        foreach ($translations as $locale => $translation) {
            $translations[$locale] = $this->getTranslation($method, $arguments, $locale);
        }

        return $translations;
    }
}
