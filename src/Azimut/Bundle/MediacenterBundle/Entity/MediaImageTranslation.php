<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-29 17:01:33
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_image_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="image")
 */
class MediaImageTranslation extends MediaTranslation
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     */
    protected $caption;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=false)
     * @Assert\NotBlank
     */
    protected $altText;

    public function getAltText()
    {
        return $this->altText;
    }

    public function setAltText($altText)
    {
        $this->altText = $altText;

        return $this;
    }

    public function getCaption()
    {
        return $this->caption;
    }

    public function setCaption($caption)
    {
        $this->caption = $caption;

        return $this;
    }
}
