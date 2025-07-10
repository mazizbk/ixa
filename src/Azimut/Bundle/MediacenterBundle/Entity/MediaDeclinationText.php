<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-24 15:46:00
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_declination_text")
 *
* @DynamicInheritanceSubClass(discriminatorValue="text")
 */
class MediaDeclinationText extends MediaDeclination
{
    public static function getMediaDeclinationType()
    {
        return 'text';
    }
}
