<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-01-25 22:18:21
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\CmsBundle\Entity\Traits\AccessRightCmsFileTrait;
use Azimut\Bundle\CmsBundle\Entity\CmsFile;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_access_right_cmsfiledocument_list")
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfiledocument_list")
 */
class AccessRightCmsFileDocumentList extends AccessRight
{
    use AccessRightCmsFileTrait;

    /**
     * @var CmsFileDocumentList
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\CmsFileDocumentList", inversedBy="accessRights")
     * @ORM\JoinColumn(name="cmsfiledocument_list_id", referencedColumnName="id", onDelete="cascade")
     */
    protected $cmsfiledocument_list;

    public static function getObjectClass()
    {
        return CmsFileDocumentList::class;
    }

    public function getObject()
    {
        return $this->cmsfiledocument_list;
    }

    public function setObject(CmsFile $object)
    {
        $this->cmsfiledocument_list = $object;
        $object->addAccessRight($this);

        return $this;
    }
}
