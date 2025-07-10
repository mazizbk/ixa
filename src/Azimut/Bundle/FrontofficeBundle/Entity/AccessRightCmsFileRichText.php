<?php
/**
 * Created by mikaelp on 12/8/2015 11:16 AM
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfilerich_text")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfilerich_text")
 */
class AccessRightCmsFileRichText extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileRichText
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\CmsFileRichText", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfilerich_text_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfilerich_text;

    public static function getObjectClass()
    {
        return CmsFileRichText::class;
    }

    public function getObject()
    {
        return $this->cmsfilerich_text;
    }

    public function setObject(CmsFile $object)
    {
        $this->cmsfilerich_text = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
