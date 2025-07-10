<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-03 14:24:45
 */

namespace Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraints\File;

/**
 * @Annotation
 */
class Video extends File
{
    public $mimeTypes = 'video/*';

    public $mimeTypesMessage = 'this.file.is.not.a.valid.video';
}
