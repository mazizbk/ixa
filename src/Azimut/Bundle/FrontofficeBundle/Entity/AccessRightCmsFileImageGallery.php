<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-04-22 10:10:55
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfileimage_gallery")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfileimage_gallery")
 */
class AccessRightCmsFileImageGallery extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileImageGallery
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\CmsFileImageGallery", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfileimagegallery_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfileimage_gallery;

    public static function getObjectClass()
    {
        return CmsFileImageGallery::class;
    }

    public function getObject()
    {
        return $this->cmsfileimage_gallery;
    }

    public function setObject(CmsFile $object)
    {
        $this->cmsfileimage_gallery = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
