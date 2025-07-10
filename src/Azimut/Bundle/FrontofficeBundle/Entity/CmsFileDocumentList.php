<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-01-11 15:58:16
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileSecondaryAttachmentsTrait;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 * @ORM\Table(name="cms_cmsfile_document_list")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="document_list")
 */
class CmsFileDocumentList extends CmsFile
{
    use CmsFileSecondaryAttachmentsTrait {
        CmsFileSecondaryAttachmentsTrait::__construct as private __constructCmsFileSecondaryAttachmentsTrait;
    }

    /**
     * @var AccessRightCmsFileDocumentList[]|ArrayCollection<AccessRightCmsFileDocumentList>
     *
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileDocumentList", mappedBy="cmsfiledocument_list")
     */
    protected $accessRights;

    public function __construct()
    {
        parent::__construct();
        $this->__constructCmsFileSecondaryAttachmentsTrait();
    }

    public static function getCmsFileType()
    {
        return 'document_list';
    }

    public static function getParentsClassesSecurityContextObject()
    {
        return Page::class;
    }
}
