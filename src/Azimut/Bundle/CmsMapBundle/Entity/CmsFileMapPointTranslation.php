<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-08-21 14:48:45
 */

namespace Azimut\Bundle\CmsMapBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Symfony\Component\Validator\Constraints as Assert;
use Azimut\Bundle\CmsBundle\Entity\CmsFileTranslation;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_cmsfile_map_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="map_point")
 */
class CmsFileMapPointTranslation extends CmsFileTranslation
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     * @Assert\NotBlank()
     */
    protected $title;

    /**
     * @var string
     *
     * @ORM\Column(type="text")
     * @Assert\NotBlank()
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
