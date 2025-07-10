<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-01-29 17:12:39
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_media_video_translation")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="video")
 */
class MediaVideoTranslation extends MediaTranslation
{
	/**
     * @var string
     *
	 * @ORM\Column(type="string", length=150, nullable=true)
	 */
	protected $caption;

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
