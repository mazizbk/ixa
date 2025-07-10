<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-11-23 14:25:39
 */

namespace Azimut\Bundle\CmsBundle\Validator\Constraints;

class HasValidMainAttachmentValidator extends AbstractHasValidAttachmentValidator
{
    protected $attachmentPropertyName = 'mainAttachment';
}
