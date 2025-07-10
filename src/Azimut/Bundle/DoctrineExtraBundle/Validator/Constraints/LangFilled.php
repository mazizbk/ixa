<?php
/**
 * Checks if all required fields of a given locale are filled.
 * Either on locales provided in optionnal parameter "requiredLocales"
 * or if a least one field of the locale has been filled.
 *
 * requiredLocales: optionnal list of mandatory locales
 * requiredFields: list of fields to checks
 *
 * NB: requiredFields takes a getter name (without prefix), so it can validate a custom method
 *
 * Usage : @LangFilled(requiredFields={"altText", "caption"})
 */

namespace Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class LangFilled extends Constraint
{
    public $noLocaleMessage = 'langfilled.no.translated.locale';
    public $incompleteLocaleMessage = 'langfilled.incomplete.locale';

    /**
     * List of required locales.
     *
     * If empty, it means "at least one"
     *
     * @var string[]
     */
    public $requiredLocales = array();

    /**
     * List of fields to be mandatory in translation.
     */
    public $requiredFields = array();

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
