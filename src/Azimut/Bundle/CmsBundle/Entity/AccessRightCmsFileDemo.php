<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-30 17:10:33
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfiledemo")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfiledemo")
 */
class AccessRightCmsFileDemo extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileDemo
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileDemo", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfiledemo_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfiledemo;

    public static function getObjectClass()
    {
        return CmsFileDemo::class;
    }

    /**
     * @return CmsFileDemo
     */
    public function getObject()
    {
        return $this->cmsfiledemo;
    }

    /**
     * @param CmsFile $object
     * @return $this
     */
    public function setObject(CmsFile $object)
    {
        $this->cmsfiledemo = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
