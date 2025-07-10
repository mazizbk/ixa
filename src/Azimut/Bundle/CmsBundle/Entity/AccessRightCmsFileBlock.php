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
 * @ORM\Table(name="cms_access_right_cmsfileblock")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfileblock")
 */
class AccessRightCmsFileBlock extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileBlock
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileBlock", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfileblock_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfileblock;

    public static function getObjectClass()
    {
        return CmsFileBlock::class;
    }

    /**
     * @return CmsFileBlock
     */
    public function getObject()
    {
        return $this->cmsfileblock;
    }

    /**
     * @param CmsFile $object
     * @return $this
     */
    public function setObject(CmsFile $object)
    {
        $this->cmsfileblock = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
