<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-11-02 15:26:18
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfilevideo")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfilevideo")
 */
class AccessRightCmsFileVideo extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileVideo
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\CmsFileVideo", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfilevideo_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfilevideo;

    public static function getObjectClass()
    {
        return CmsFileVideo::class;
    }

    public function getObject()
    {
        return $this->cmsfilevideo;
    }

    public function setObject(CmsFile $object)
    {
        $this->cmsfilevideo = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
