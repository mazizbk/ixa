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
 * @ORM\Table(name="mediacenter_media_presentation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="presentation")
 */
class MediaPresentation extends Media
{
    protected static $mimeTypes = array(
        '#^application/vnd.ms-powerpoint#',
        '#^application/vnd.oasis.opendocument.presentation*#',
        '#^application/vnd.openxmlformats-officedocument.presentationml.*#'
    );

    const FILE_TYPE_HINT_MESSAGE = 'mediacenter.file.type.hints.message.media.presentation';

    protected static $embedUrls = [
        '#^.*<iframe.*src="//v.calameo.com/.*</iframe>.*#',
        '#^<iframe.*src="https://www.slideshare.net/slideshow/embed_code/.*</iframe>.*#'
    ];

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_media"})
     */
    protected $author;

    public static function getMediaType()
    {
        return 'presentation';
    }

    public static function getCssIcon()
    {
        return 'glyphicon glyphicon-pro glyphicon-pro-projector';
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
