<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-07-28
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileSecondaryAttachmentsTrait;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 * @ORM\Table(name="cms_cmsfile_image_gallery")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="image_gallery")
 */
class CmsFileImageGallery extends CmsFile
{
    use CmsFileSecondaryAttachmentsTrait {
        CmsFileSecondaryAttachmentsTrait::__construct as private __constructCmsFileSecondaryAttachmentsTrait;
    }

    /**
     * @var AccessRightCmsFileImageGallery[]|ArrayCollection<AccessRightCmsFileImageGallery>
     *
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileImageGallery", mappedBy="cmsfileimage_gallery")
     */
    protected $accessRights;

    public function __construct()
    {
        parent::__construct();
        $this->__constructCmsFileSecondaryAttachmentsTrait();
    }

    public static function getCmsFileType()
    {
        return 'image_gallery';
    }

    public static function getParentsClassesSecurityContextObject()
    {
        return Page::class;
    }
}
