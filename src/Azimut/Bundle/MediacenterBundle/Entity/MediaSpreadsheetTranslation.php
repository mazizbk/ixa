<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-08-04
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_spreadsheet_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="spreadsheet")
 */
class MediaSpreadsheetTranslation extends MediaTranslation
{
}
