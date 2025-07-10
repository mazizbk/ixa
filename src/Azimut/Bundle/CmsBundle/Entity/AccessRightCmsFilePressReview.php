<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-04-13 10:41:24
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfilepress_review")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfilepress_review")
 */
class AccessRightCmsFilePressReview extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFilePressReview
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFilePressReview", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfilepress_review_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfilepress_review;

    public static function getObjectClass()
    {
        return CmsFilePressReview::class;
    }

    /**
     * @return CmsFilePressReview
     */
    public function getObject()
    {
        return $this->cmsfilepress_review;
    }

    /**
     * @param CmsFile $object
     * @return $this
     */
    public function setObject(CmsFile $object)
    {
        $this->cmsfilepress_review = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
