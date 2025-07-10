<?php
/**
 * Created by mikaelp on 12/8/2015 11:16 AM
 */

namespace Azimut\Bundle\CmsContactBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfilecontact")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfilecontact")
 */
class AccessRightCmsFileContact extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileContact
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\CmsContactBundle\Entity\CmsFileContact", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfilecontact_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfilecontact;

    public static function getObjectClass()
    {
        return CmsFileContact::class;
    }

    public function getObject()
    {
        return $this->cmsfilecontact;
    }

    public function setObject(CmsFileContact $object)
    {
        $this->cmsfilecontact = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
