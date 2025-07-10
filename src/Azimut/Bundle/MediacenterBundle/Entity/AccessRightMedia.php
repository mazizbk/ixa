<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:    2015-11-25 11:02:17
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\Groups;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_access_right_media")
 * @DynamicInheritanceSubClass(discriminatorValue="media")
 */
class AccessRightMedia extends AccessRight
{
    /**
     * @var Media
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MediacenterBundle\Entity\Media", inversedBy="accessRights")
     * @ORM\JoinColumn(name="media_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $media;

    public function getMedia()
    {
        return $this->media;
    }

    public function setMedia(Media $media)
    {
        $this->media = $media;
        $media->addAccessRight($this);

        return $this;
    }

    public function getObject()
    {
        return $this->media;
    }

    public function setObject($media)
    {
        return $this->media = $media;
    }

    public static function getObjectClass()
    {
        return Media::class;
    }

    /**
     * @VirtualProperty
     * @Groups({"list_access_rights"})
     */
    public function getObjectId()
    {
        if (null === $this->getObject()) {
            return null;
        }

        return $this->getObject()->getId();
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_access_right", "list_access_rights"})
     */
    public function getAccessRightType()
    {
        return 'media';
    }
}
