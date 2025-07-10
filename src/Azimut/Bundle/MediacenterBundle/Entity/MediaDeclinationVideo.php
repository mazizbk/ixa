<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-10-31
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints\Video;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_declination_video")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="video")
 */
class MediaDeclinationVideo extends MediaDeclination
{
    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true, options={"unsigned":true})
     *
     * @Groups({"list_media_declinations","detail_media_declination"})
     */
    protected $pixelWidth;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true, options={"unsigned":true})
     *
     * @Groups({"list_media_declinations","detail_media_declination"})
     */
    protected $pixelHeight;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"detail_media_declination"})
     */
    protected $datetimeOriginal;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255,nullable=true)
     *
     * @Groups({"detail_media_declination"})
     */
    protected $author;

    /**
     * @var string
     *
     * @ORM\Column(type="text", nullable=true)
     * @Groups({"detail_media"})
     * @Assert\NotBlank(groups={"embedHtmlRequired"})
     * @Assert\Regex(
     *     pattern="/<iframe[^>]+>[^<]*<\/iframe>/",
     *     message="embed.html.must.contain.iframe"
     * )
     */
    protected $embedHtml;

    public static function getMediaDeclinationType()
    {
        return 'video';
    }

    public function getPixelWidth()
    {
        return $this->pixelWidth;
    }

    public function setPixelWidth($pixelWidth)
    {
        $this->pixelWidth = $pixelWidth;

        return $this;
    }

    public function getPixelHeight()
    {
        return $this->pixelHeight;
    }

    public function setPixelHeight($pixelHeight)
    {
        $this->pixelHeight = $pixelHeight;

        return $this;
    }

    public function getDateTimeOriginal()
    {
        return $this->datetimeOriginal;
    }

    public function setDateTimeOriginal($datetimeOriginal)
    {
        if (!$datetimeOriginal instanceof \DateTime) {
            $datetimeOriginal = new \DateTime($datetimeOriginal);
        }
        $this->datetimeOriginal = $datetimeOriginal;

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

    public function getEmbedHtml()
    {
        return $this->embedHtml;
    }

    public function setEmbedHtml($embedHtml)
    {
        $embedHtml = preg_replace('/width="\d+"/', 'width="100%"', $embedHtml);
        $embedHtml = preg_replace('/height="\d+"/', 'height="100%"', $embedHtml);

        $this->embedHtml = $embedHtml;

        return $this;
    }

    public function isFileRequired()
    {
        return false;
    }

    /**
     * Because file property is in parent class we cannot assert that file should be an image via annotations, so
     * we use the PHP way to declare the constraint. This avoid check file type in ImageManager.
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('file', new Video());
    }
}
