<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-16 17:14:57
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_declination_generic_embed_html_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="generic_embed_html")
 */
class MediaDeclinationGenericEmbedHtmlTranslation extends MediaDeclinationTranslation
{

}
