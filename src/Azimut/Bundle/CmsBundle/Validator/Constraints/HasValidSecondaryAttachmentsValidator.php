<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-09-17 18:14:07
 */

namespace Azimut\Bundle\CmsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class HasValidSecondaryAttachmentsValidator extends AbstractHasValidAttachmentsValidator
{
    protected $attachmentPropertyName = 'secondaryAttachments';
}
