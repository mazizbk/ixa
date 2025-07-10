<?php
/**
 * Created by mikaelp on 12/8/2015 11:16 AM
 */

namespace Azimut\Bundle\CmsMapBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfilemap_point")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfilemap_point")
 */
class AccessRightCmsFileMapPoint extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileMapPoint
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\CmsMapBundle\Entity\CmsFileMapPoint", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfilemappoint_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfilemap_point;

    public static function getObjectClass()
    {
        return CmsFileMapPoint::class;
    }

    public function getObject()
    {
        return $this->cmsfilemap_point;
    }

    public function setObject(CmsFile $object)
    {
        $this->cmsfilemap_point = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
