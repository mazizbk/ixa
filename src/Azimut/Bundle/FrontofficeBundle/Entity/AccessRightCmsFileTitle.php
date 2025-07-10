<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-30 17:10:33
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfiletitle")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfiletitle")
 */
class AccessRightCmsFileTitle extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileTitle
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\CmsFileTitle", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfiletitle_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfiletitle;

    public static function getObjectClass()
    {
        return CmsFileTitle::class;
    }

    public function getObject()
    {
        return $this->cmsfiletitle;
    }

    public function setObject(CmsFile $object)
    {
        $this->cmsfiletitle = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
