<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Validator\Constraint;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueEmailParticipation extends Constraint
{
    public $message = 'montgolfiere.questionnaire.you_have_already_participated';

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
