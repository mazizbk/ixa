<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-06-23 13:48:07
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_other")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="other")
 */
class MediaOther extends Media
{
    protected static $mimeTypes = array();

    public static function getMediaType()
    {
        return 'other';
    }
}
