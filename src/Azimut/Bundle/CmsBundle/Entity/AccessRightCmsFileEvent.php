<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-17 16:38:39
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfileevent")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfileevent")
 */
class AccessRightCmsFileEvent extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileEvent
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileEvent", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfileevent_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfileevent;

    public static function getObjectClass()
    {
        return CmsFileEvent::class;
    }

    /**
     * @return CmsFileEvent
     */
    public function getObject()
    {
        return $this->cmsfileevent;
    }

    /**
     * @param CmsFile $object
     * @return $this
     */
    public function setObject(CmsFile $object)
    {
        $this->cmsfileevent = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
