<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-12 17:26:03
 */

namespace Azimut\Bundle\ModerationBundle\Entity;

use Azimut\Bundle\FormExtraBundle\Model\Geolocation;
use Azimut\Bundle\FormExtraBundle\Model\MapPointPosition;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\ModerationBundle\Annotation\CmsFileConverterProperty;
use Azimut\Bundle\CmsMapBundle\Entity\CmsFileMapPoint;

/**
 * @ORM\Entity
 * @DynamicInheritanceSubClass(discriminatorValue="map_point")
 */
class CmsFileMapPointBuffer extends CmsFileBuffer
{
    const TARGET_CMSFILE_CLASS = CmsFileMapPoint::class;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     * @Assert\NotBlank()
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $text;

    /**
     * @var Geolocation
     *
     * @ORM\Column(type="object", nullable=false)
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $geolocation;

    /**
     * @var MapPointPosition
     *
     * @ORM\Column(type="object", nullable=false)
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $position;


    public function getName($locale = null)
    {
        return $this->title;
    }

    public static function getCmsFileBufferType()
    {
        return 'map_point';
    }

    public static function hasFile()
    {
        return true;
    }
}
