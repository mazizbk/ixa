<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-07-03
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Azimut\Bundle\FormExtraBundle\Model\Geolocation;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints as AzimutAssert;
use Symfony\Component\HttpFoundation\File\File;
use Azimut\Bundle\MediacenterBundle\Entity\Repository\MediaRepository;
use Azimut\Bundle\MediacenterBundle\Entity\Repository\MediaDeclinationRepository;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_image")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="image")
 * @AzimutAssert\LangFilled(requiredFields={"altText"})
 */
class MediaImage extends Media
{
    protected static $mimeTypes = array(
        '#^image/.*#',
        '#^application/photoshop#',
        '#^application/illustrator#',
        '#^application/postscript#',
        '#^application/eps#',
        '#^application/x-eps#'
    );

    const FILE_TYPE_HINT_MESSAGE = 'mediacenter.file.type.hints.message.media.image';

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_media"})
     */
    protected $author;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=100, nullable=true)
     * @Groups({"detail_media"})
     */
    protected $copyright;

    /**
     * @var Geolocation
     *
     * @ORM\Column(type="object", nullable=true)
     *
     * @Groups({"detail_media"})
     */
    protected $geolocation;

    public static function getMediaType()
    {
        return 'image';
    }

    public static function getCssIcon()
    {
        return 'glyphicon glyphicon-picture';
    }

    public function setRequiredFieldsFromFile(File $file)
    {
        parent::setRequiredFieldsFromFile($file);

        $this->setAltText($this->getName());

        return $this;
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_media"})
     */
    public function getCaption($locale = null)
    {
        /** @var MediaImageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getCaption();
    }

    public function setCaption($caption, $locale = null)
    {
        /** @var MediaImageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setCaption(ucfirst($caption));

        return $this;
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_media"})
     */
    public function getAltText($locale = null)
    {
        /** @var MediaImageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getAltText();
    }

    public function setAltText($altText, $locale = null)
    {
        /** @var MediaImageTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setAltText(ucfirst($altText));

        return $this;
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

    public function getCopyright()
    {
        return $this->copyright;
    }

    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;

        return $this;
    }

    public function getGeolocation()
    {
        return $this->geolocation;
    }

    public function setGeolocation($geolocation)
    {
        $this->geolocation = $geolocation;

        return $this;
    }

    /**
     * @VirtualProperty()
     * @Groups({"list_medias", "detail_folder", "detail_media"})
     */
    public function getPixelWidth()
    {
        return $this->getMainDeclination()->getPixelWidth();
    }

    /**
     * @VirtualProperty()
     * @Groups({"list_medias", "detail_folder", "detail_media"})
     */
    public function getPixelHeight()
    {
        return $this->getMainDeclination()->getPixelHeight();
    }
}
