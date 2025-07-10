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
 * @ORM\Table(name="cms_access_right_cmsfilearticle")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfilearticle")
 */
class AccessRightCmsFileArticle extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileArticle
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileArticle", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfilearticle_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfilearticle;

    public static function getObjectClass()
    {
        return CmsFileArticle::class;
    }

    /**
     * @return CmsFileArticle
     */
    public function getObject()
    {
        return $this->cmsfilearticle;
    }

    /**
     * @param CmsFile $object
     * @return $this
     */
    public function setObject(CmsFile $object)
    {
        $this->cmsfilearticle = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
