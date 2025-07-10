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
 * @ORM\Table(name="mediacenter_media_archive")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="archive")
 */
class MediaArchive extends Media
{
    protected static $mimeTypes = array(
        '#^application/x-tar#',
        '#^application/x-bzip2#',
        '#^application/x-gzip#',
        '#^application/x-lzip#',
        '#^application/x-lzma#',
        '#^application/x-lzop#',
        '#^application/x-xz#',
        //'#^application/x-compress#',

        //'#^application/x-7z-compressed#',
        //'#^application/x-ace-compressed#',
        //'#^application/vnd.ms-cab-compressed#',
        //'#^application/x-cfs-compressed#',
        //'#^application/x-rar-compressed#',
        '#^application/*compress*#',

        '#^application/vnd.android.package-archive#',
        '#^application/x-apple-diskimage#',

        '#^application/x-arj#',
        '#^application/x-lzh#',
        '#^application/x-lzx#',
        '#^application/x-stuffit#',
        '#^application/x-stuffitx#',
        '#^application/x-gtar#',
        '#^application/zip#'
    );

    const FILE_TYPE_HINT_MESSAGE = 'mediacenter.file.type.hints.message.media.archive';

    public static function getMediaType()
    {
        return 'archive';
    }

    public static function getCssIcon()
    {
        return 'glyphicon glyphicon-pro glyphicon-pro-compressed';
    }
}
