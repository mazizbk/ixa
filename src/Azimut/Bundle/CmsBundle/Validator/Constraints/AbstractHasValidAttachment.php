<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-05-28 11:03:50
 */

namespace Azimut\Bundle\CmsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
abstract class AbstractHasValidAttachment extends Constraint
{
    public $message = 'this.value.must.be.of.type.%type_name%';

    public $acceptedClasses = [];

    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
