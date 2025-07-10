<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-08-01
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_declination_animation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="animation")
 */
class MediaDeclinationAnimation extends MediaDeclination
{
    public static function getMediaDeclinationType()
    {
        return 'animation';
    }
}
