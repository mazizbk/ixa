<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-07-03
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_video")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="video")
 */
class MediaVideo extends Media
{
    protected static $mimeTypes = array(
        '#^video/.*#'
    );

    protected static $embedUrls = [
        '#^<iframe.*src="https://www.youtube.com/embed.*</iframe>#',
        '#^<iframe.*src="//www.dailymotion.com/embed.*</iframe>.*#',
        '#^<iframe.*src="https://player.vimeo.com/video/.*</iframe>.*#',
        '#^<iframe.*src="https://www.facebook.com/plugins/video.*</iframe>.*#'
    ];

    const FILE_TYPE_HINT_MESSAGE = 'mediacenter.file.type.hints.message.media.video';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"detail_media"})
     */
    protected $copyright;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     */
    protected $productionDate;

    public static function getMediaType()
    {
        return 'video';
    }

    public static function getCssIcon()
    {
        return 'glyphicon glyphicon-film';
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_media"})
     */
    public function getCaption($locale = null)
    {
        /** @var MediaVideoTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getCaption();
    }

    public function setCaption($caption, $locale = null)
    {
        /** @var MediaVideoTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setCaption(ucfirst($caption));

        return $this;
    }

    public function getCopyright()
    {
        return $this->copyright;
    }

    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;

        return $this;
    }

    public function getProductionDate()
    {
        return $this->productionDate;
    }

    public function setProductionDate($productionDate)
    {
        $this->productionDate = $productionDate;

        return $this;
    }
}
