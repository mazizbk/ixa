<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-05-28 10:54:38
 */

namespace Azimut\Bundle\CmsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Translation\TranslatorInterface;

abstract class AbstractHasValidAttachmentsValidator extends ConstraintValidator
{
    /**
     * @var string
     */
    protected $attachmentPropertyName;

    /**
     * @var TranslatorInterface
     */
    protected $translator;

    /**
     * @param TranslatorInterface $translator
     */
    public function __construct(TranslatorInterface $translator)
    {
        $this->translator = $translator;
    }

    public function validate($protocol, Constraint $constraint)
    {
        if (null == $this->attachmentPropertyName) {
            throw new \Exception("attachmentPropertyName must be defined");
        }

        $getter = 'get'.str_replace(' ', '', ucwords(str_replace('_', ' ', $this->attachmentPropertyName)));

        if (null == $protocol->$getter()) {
            return;
        }

        $acceptedClasses = $constraint->acceptedClasses;

        if (0 == count($acceptedClasses)) {
            return;
        }

        $acceptedTypeNames = [];
        $invalidAttachmentKeys = [];

        foreach ($acceptedClasses as $class) {
            $acceptedTypeNames[] = $this->translator->trans($class::getMediaDeclinationType());
        }

        foreach ($protocol->$getter() as $key => $attachment) {
            $isValidAttachment = false;

            foreach ($acceptedClasses as $class) {
                if ($attachment->getMediaDeclination() instanceof $class) {
                    $isValidAttachment = true;
                    break;
                }
            }

            if (!$isValidAttachment) {
                $invalidAttachmentKeys[] = $key;
            }
        }

        foreach (array_unique($invalidAttachmentKeys) as $invalidAttachmentKey) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('%type_name%', implode(', ', $acceptedTypeNames))
                ->atPath($this->attachmentPropertyName.'['. $invalidAttachmentKey .']')
                ->addViolation();
        }
    }
}
