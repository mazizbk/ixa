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
 * @ORM\Table(name="mediacenter_media_audio")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="audio")
 */
class MediaAudio extends Media
{
    protected static $mimeTypes = array(
        '#^audio/.*#'
    );

    const FILE_TYPE_HINT_MESSAGE = 'mediacenter.file.type.hints.message.media.audio';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_media"})
     */
    protected $author;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"detail_media"})
     */
    protected $duration;

    public static function getMediaType()
    {
        return 'audio';
    }

    public static function getCssIcon()
    {
        return 'glyphicon glyphicon-headphones';
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

    public function getDuration()
    {
        return $this->duration;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;

        return $this;
    }
}
