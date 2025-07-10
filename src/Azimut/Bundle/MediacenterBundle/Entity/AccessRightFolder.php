<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:    2015-11-26 09:32:17
 */

namespace Azimut\Bundle\MediacenterBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\Groups;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="mediacenter_access_right_folder")
 * @DynamicInheritanceSubClass(discriminatorValue="folder")
 */
class AccessRightFolder extends AccessRight
{
    /**
     * @var Folder
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\MediacenterBundle\Entity\Folder", inversedBy="accessRights")
     * @ORM\JoinColumn(name="folder_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $folder;

    public function getFolder()
    {
        return $this->folder;
    }

    public function setFolder(Folder $folder)
    {
        $this->folder = $folder;
        $folder->addAccessRight($this);

        return $this;
    }

    public function getObject()
    {
        return $this->folder;
    }

    public function setObject($folder)
    {
        return $this->folder = $folder;
    }

    public static function getObjectClass()
    {
        return Folder::class;
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
        return 'folder';
    }
}
