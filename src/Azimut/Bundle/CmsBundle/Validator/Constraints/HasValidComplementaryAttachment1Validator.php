<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-05-28 10:51:58
 */

namespace Azimut\Bundle\CmsBundle\Validator\Constraints;

class HasValidComplementaryAttachment1Validator extends AbstractHasValidAttachmentValidator
{
    protected $attachmentPropertyName = 'complementaryAttachment1';
}
