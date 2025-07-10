<?php
/**
 * Created by mikaelp on 2018-11-07 10:54 AM
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfiletitleandtext")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfiletitleandtext")
 */
class AccessRightCmsFileTitleAndText extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileTitleAndText
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\CmsFileTitleAndText", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfiletitleandtext_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfiletitle_and_text;

    public static function getObjectClass()
    {
        return CmsFileTitleAndText::class;
    }

    public function getObject()
    {
        return $this->cmsfiletitle_and_text;
    }

    public function setObject(CmsFile $object)
    {
        $this->cmsfiletitle_and_text = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
