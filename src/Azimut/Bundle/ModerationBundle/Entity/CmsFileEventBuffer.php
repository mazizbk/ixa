<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-12 17:17:56
 */


namespace Azimut\Bundle\ModerationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\ModerationBundle\Annotation\CmsFileConverterProperty;
use Azimut\Bundle\CmsBundle\Entity\CmsFileEvent;

/**
 * @ORM\Entity
 * @DynamicInheritanceSubClass(discriminatorValue="event")
 */
class CmsFileEventBuffer extends CmsFileBuffer
{
    const TARGET_CMSFILE_CLASS = CmsFileEvent::class;

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
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=false)
     * @Assert\NotBlank()
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $eventStartDatetime;

    /**
     * @var \DateTime
     *
     * @ORM\Column(type="datetime", nullable=true)
     *
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $eventEndDatetime;


    public function getName($locale = null)
    {
        return $this->title;
    }

    public static function getCmsFileBufferType()
    {
        return 'event';
    }

    public static function hasFile()
    {
        return true;
    }
}
