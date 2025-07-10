<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-29 18:17:32
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_declination_video_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="video")
 */
class MediaDeclinationVideoTranslation extends MediaDeclinationTranslation
{
}
