<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 11:40:08
 */

namespace Azimut\Bundle\ModerationBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\ModerationBundle\Annotation\CmsFileConverterProperty;
use Azimut\Bundle\CmsBundle\Entity\CmsFileArticle;

/**
 * @ORM\Entity
 * @DynamicInheritanceSubClass(discriminatorValue="article")
 */
class CmsFileArticleBuffer extends CmsFileBuffer
{
    const TARGET_CMSFILE_CLASS = CmsFileArticle::class;

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
    public $author;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
     * @Groups({"detail_cms_file_buffer"})
     * @CmsFileConverterProperty()
     */
    public $text;


    public function getName($locale = null)
    {
        return $this->title;
    }

    public static function getCmsFileBufferType()
    {
        return 'article';
    }

    public static function hasFile()
    {
        return true;
    }
}
