<?php

namespace Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints;

use Azimut\Bundle\DoctrineExtraBundle\Entity\TranslatableEntityInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @Annotation
 */
class LangFilledValidator extends ConstraintValidator
{
    /**
     * {@inheritdoc}
     * @param TranslatableEntityInterface $value
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof LangFilled) {
            throw new \InvalidArgumentException(sprintf('Expected a LangFilled, got a %s.', get_class($constraint)));
        }

        $locales = $constraint->requiredLocales;
        $fields  = $constraint->requiredFields;

        if (!count($locales)) {
            $locales = array_keys($value->getTranslations()->toArray());
        }

        if (!count($fields)) {
            throw new \LogicException('Constraint LangFilled requires at least one field (option @LangFilled(requiredFields={...})');
        }

        /** @var ExecutionContextInterface $ctx */
        $ctx = $this->context;
        if (count($locales)) {
            foreach ($locales as $locale) {
                $emptyFields = $this->validateLocale($value, $fields, $locale);
                foreach ($emptyFields as $emptyField) {
                    $ctx->buildViolation($constraint->incompleteLocaleMessage)
                        ->atPath($emptyField)
                        ->setParameter('{{ locale }}', $locale)
                        ->setInvalidValue($value)
                        ->addViolation()
                    ;
                }
            }

            return;
        }

        $found = false;
        foreach ($value->getTranslations() as $translation) {
            if ($this->validateLocale($value, $fields, $translation->getLocale())) {
                $found = true;

                break;
            }
        }

        // avoid duplicate message
        if (!$found) {
            $noLocaleMessageAlreadySet = false;

            if (null != $this->context->getViolations()) {
                foreach ($this->context->getViolations() as $violation) {
                    if ($violation->getMessageTemplate() == $constraint->noLocaleMessage) {
                        $noLocaleMessageAlreadySet = true;
                    }
                }
            }

            if (!$noLocaleMessageAlreadySet) {
                $this->context->addViolation($constraint->noLocaleMessage);
            }
        }
    }

    private function validateLocale($value, $fields, $locale)
    {
        $emptyFields = array();
        foreach ($fields as $field) {
            $method = 'get'.ucfirst($field);
            if (!$value->$method($locale)) {
                $emptyFields[] = $field;
            }
        }

        return $emptyFields;
    }
}
