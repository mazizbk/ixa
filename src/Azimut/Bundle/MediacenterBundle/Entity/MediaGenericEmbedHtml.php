<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-16 17:13:15
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_generic_embed_html")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="generic_embed_html")
 */
class MediaGenericEmbedHtml extends Media
{
    protected static $mimeTypes = [];

    public static function getMediaType()
    {
        return 'generic_embed_html';
    }

    public static function getCssIcon()
    {
        return 'glyphicon glyphicon-pro glyphicon-pro-embed-close';
    }
}
