<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-12 17:23:00
 */

namespace Azimut\Bundle\ModerationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\ModerationBundle\Annotation\CmsFileConverterProperty;
use Azimut\Bundle\CmsContactBundle\Entity\CmsFileContact;

/**
 * @ORM\Entity
 * @DynamicInheritanceSubClass(discriminatorValue="contact")
 */
class CmsFileContactBuffer extends CmsFileBuffer
{
    const TARGET_CMSFILE_CLASS = CmsFileContact::class;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $firstName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $lastName;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=300, nullable=true)
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $address;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $zipCode;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $city;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $country;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="date", nullable=true)
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $birthday;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $phone;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $email;


    public function getName($locale = null)
    {
        return $this->firstName.' '.$this->lastName;
    }

    public static function getCmsFileBufferType()
    {
        return 'contact';
    }

    public static function hasFile()
    {
        return false;
    }
}
