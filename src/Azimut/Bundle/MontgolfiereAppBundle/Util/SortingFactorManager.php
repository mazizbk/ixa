<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Util;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactor;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue;

class SortingFactorManager
{
    /**
     * @var array
     */
    protected $questionnaireLocales;

    public function __construct(array $questionnaireLocales)
    {
        $this->questionnaireLocales = $questionnaireLocales;
    }

    public function getSortingFactorName(string $requestLocale, CampaignSortingFactor $sortingFactor): string
    {
        $locales = $this->getOrderedLocales($requestLocale);
        $names = $sortingFactor->getNames();

        return self::getFirstExistingKey($locales, $names);
    }

    public function getSortingFactorValueName(string $requestLocale, CampaignSortingFactorValue $value): string
    {
        $locales = $this->getOrderedLocales($requestLocale);
        $names = $value->getLabels();

        return self::getFirstExistingKey($locales, $names);
    }

    protected function getOrderedLocales(string $requestLocale): array
    {
        $fallbackLocales = $this->questionnaireLocales;

        $locales = $fallbackLocales;
        array_unshift($locales, $requestLocale);

        return $locales;
    }

    protected static function getFirstExistingKey(array $array, array $orderedKeys): string
    {
        foreach ($array as $locale) {
            if (array_key_exists($locale, $orderedKeys)) {
                return $orderedKeys[$locale];
            }
        }

        return array_shift($orderedKeys);
    }
}
