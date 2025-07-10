<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-10-31
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints\Image;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_declination_image")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="image")
 */
class MediaDeclinationImage extends MediaDeclination
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
     * @ORM\Column(type="string", length=255,nullable=true)
     *
     * @Groups({"detail_media_declination"})
     */
    protected $software;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255,nullable=true)
     *
     * @Groups({"detail_media_declination"})
     */
    protected $resolution;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255,nullable=true)
     *
     * @Groups({"detail_media_declination"})
     */
    protected $deviceMaker;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=255,nullable=true)
     *
     * @Groups({"detail_media_declination"})
     */
    protected $deviceModel;

    /**
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true, options={"unsigned":true}))
     *
     * @Groups({"detail_media_declination"})
     */
    protected $orientation;

    public static function getMediaDeclinationType()
    {
        return 'image';
    }

    public function generateThumb()
    {
        if (in_array($this->fileExtension, ['JPEG', 'PNG', 'GIF'])) {
            $this->setThumb($this->getPath());
        }
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
        if (! $datetimeOriginal instanceof \DateTime) {
            $datetimeOriginal = \DateTime::createFromFormat('Y:m:d H:i:s', $datetimeOriginal);
        }
        if ($datetimeOriginal) {
            $this->datetimeOriginal = $datetimeOriginal;
        }

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

    public function getSoftware()
    {
        return $this->software;
    }

    public function setSoftware($software)
    {
        $this->software = $software;

        return $this;
    }

    public function getResolution()
    {
        return $this->resolution;
    }

    public function setResolution($resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }

    public function getDeviceMaker()
    {
        return $this->deviceMaker;
    }

    public function setDeviceMaker($deviceMaker)
    {
        $this->deviceMaker = $deviceMaker;

        return $this;
    }

    public function getDeviceModel()
    {
        return $this->deviceModel;
    }

    public function setDeviceModel($deviceModel)
    {
        $this->deviceModel = $deviceModel;

        return $this;
    }

    public function getOrientation()
    {
        return $this->orientation;
    }

    public function setOrientation($orientation)
    {
        //if(!is_int($orientation) || !($orientation>0 && $orientation<9)) throw new \InvalidArgumentException('Orientation must be an integer greater than 0 and lower than 10 "'.$orientation.'" given');

        if(!is_int($orientation) || !($orientation > 0 && $orientation < 9)) {
            $orientation = null;
        }

        $this->orientation = $orientation;

        return $this;
    }

    /**
     * Because file property is in parent class we cannot assert that file should be an image via annotations, so
     * we use the PHP way to declare the constraint. This avoid check file type in ImageManager.
     */
    public static function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addPropertyConstraint('file', new Image());
    }
}
