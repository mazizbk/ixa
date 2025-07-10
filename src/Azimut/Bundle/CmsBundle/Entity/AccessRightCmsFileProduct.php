<?php
/**
 * Created by mikaelp on 12/8/2015 11:16 AM
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfileproduct")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfileproduct")
 */
class AccessRightCmsFileProduct extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileProduct
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileProduct", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfileproduct_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfileproduct;

    public static function getObjectClass()
    {
        return CmsFileProduct::class;
    }

    /**
     * @return CmsFileProduct
     */
    public function getObject()
    {
        return $this->cmsfileproduct;
    }

    /**
     * @param CmsFile $object
     * @return $this
     */
    public function setObject(CmsFile $object)
    {
        $this->cmsfileproduct = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
