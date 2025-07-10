<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-07-24 15:34:00
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_text")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="text")
 */
class MediaText extends Media
{
    protected static $mimeTypes = array(
        '#^text/.*#', //'text/plain',
        '#^application/msword#',
        '#^application/vnd.openxmlformats-officedocument.wordprocessingml.*#',
        '#^application/pdf#',
        '#^application/x-mspublisher#',
        '#^application/vnd.oasis.opendocument.text*#'
    );

    const FILE_TYPE_HINT_MESSAGE = 'mediacenter.file.type.hints.message.media.text';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_media"})
     */
    protected $author;

    public static function getMediaType()
    {
        return 'text';
    }

    public static function getCssIcon()
    {
        return 'glyphicon glyphicon-pro glyphicon-pro-notes';
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
