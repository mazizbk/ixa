<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-08-01
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_spreadsheet")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="spreadsheet")
 */
class MediaSpreadsheet extends Media
{
    protected static $mimeTypes = array(
        '#^application/vnd.ms-excel#',
        '#^application/vnd.oasis.opendocument.spreadsheet*#',
        '#^application/vnd.openxmlformats-officedocument.spreadsheetml.*#'
    );

    const FILE_TYPE_HINT_MESSAGE = 'mediacenter.file.type.hints.message.media.spreadsheet';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_media"})
     */
    protected $author;

    public static function getMediaType()
    {
        return 'spreadsheet';
    }

    public static function getCssIcon()
    {
        return 'glyphicon glyphicon-pro glyphicon-pro-table';
    }

    public function getAuthor()
    {
        return $this->author;
    }

    public function setAuthor($author)
    {
        $this->author = $author;

        return $this;
    }
}
