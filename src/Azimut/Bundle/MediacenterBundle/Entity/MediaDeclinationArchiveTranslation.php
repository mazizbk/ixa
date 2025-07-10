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
 * @ORM\Table(name="mediacenter_media_declination_archive_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="archive")
 */
class MediaDeclinationArchiveTranslation extends MediaDeclinationTranslation
{
}
