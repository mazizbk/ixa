<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-04-22 10:10:31
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfileimage")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfileimage")
 */
class AccessRightCmsFileImage extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileImage
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\CmsFileImage", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfileimage_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfileimage;

    public static function getObjectClass()
    {
        return CmsFileImage::class;
    }

    public function getObject()
    {
        return $this->cmsfileimage;
    }

    public function setObject(CmsFile $object)
    {
        $this->cmsfileimage = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
