<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-06-23 14:23:19
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_declination_other_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="other")
 */
class MediaDeclinationOtherTranslation extends MediaDeclinationTranslation
{
}
