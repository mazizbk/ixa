<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-01-11 15:59:21
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfiledocument")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfiledocument")
 */
class AccessRightCmsFileDocument extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileDocument
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\CmsFileDocument", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfiledocument_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfiledocument;

    public static function getObjectClass()
    {
        return CmsFileDocument::class;
    }

    public function getObject()
    {
        return $this->cmsfiledocument;
    }

    public function setObject(CmsFile $object)
    {
        $this->cmsfiledocument = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
