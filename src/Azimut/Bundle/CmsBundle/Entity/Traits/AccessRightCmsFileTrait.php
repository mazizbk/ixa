<?php
/**
 * Created by mikaelp on 12/8/2015 10:00 AM
 */

namespace Azimut\Bundle\CmsBundle\Entity\Traits;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

/**
 * @property CmsFile $cmsfileinfo
 */
trait AccessRightCmsFileTrait
{
    /**
     * @return CmsFile
     */
    abstract public function getObject();
    abstract public function setObject(CmsFile $object);

    public static function getObjectClass()
    {
        return CmsFile::class;
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
        return 'cmsfile'.$this->getObject()->getCmsFileType();
    }
}
