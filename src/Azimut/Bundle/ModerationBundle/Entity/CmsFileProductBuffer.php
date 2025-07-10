<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-12 09:52:31
 */

namespace Azimut\Bundle\ModerationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\ModerationBundle\Annotation\CmsFileConverterProperty;
use Azimut\Bundle\CmsBundle\Entity\CmsFileProduct;

/**
 * @ORM\Entity
 * @DynamicInheritanceSubClass(discriminatorValue="product")
 */
class CmsFileProductBuffer extends CmsFileBuffer
{
    const TARGET_CMSFILE_CLASS = CmsFileProduct::class;

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
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $subtitle;

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
     * @var integer
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $price;


    public function getName($locale = null)
    {
        return $this->title;
    }

    public static function getCmsFileBufferType()
    {
        return 'product';
    }

    public static function hasFile()
    {
        return true;
    }
}
