<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-09-22 10:19:03
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfiletext")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfiletext")
 */
class AccessRightCmsFileText extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileText
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\CmsFileText", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfiletext_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfiletext;

    public static function getObjectClass()
    {
        return CmsFileText::class;
    }

    public function getObject()
    {
        return $this->cmsfiletext;
    }

    public function setObject(CmsFile $object)
    {
        $this->cmsfiletext = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
