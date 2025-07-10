<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-03 14:26:56
 */

namespace Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\FileValidator;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class VideoValidator extends FileValidator
{
    /**
     * {@inheritdoc}
     */
    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof Video) {
            throw new UnexpectedTypeException($constraint, __NAMESPACE__.'\Video');
        }

        $violations = count($this->context->getViolations());

        parent::validate($value, $constraint);

        $failed = count($this->context->getViolations()) !== $violations;

        if ($failed || null === $value || '' === $value) {
            return;
        }
    }
}
