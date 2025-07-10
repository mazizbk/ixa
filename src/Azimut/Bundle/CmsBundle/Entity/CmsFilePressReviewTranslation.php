<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-07-28
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_cmsfile_press_review_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="press_review")
 */
class CmsFilePressReviewTranslation extends CmsFileTranslation
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @ORM\Column(type="text")
     */
    protected $text;

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = ucfirst($title);

        return $this;
    }

    public function getText()
    {
        return $this->text;
    }

    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }
}
