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
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileMainAttachmentTrait;
use Azimut\Bundle\CmsBundle\Validator\Constraints as AzimutCmsAssert;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 * @ORM\Table(name="cms_cmsfile_document")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="document")
 * @AzimutCmsAssert\HasValidMainAttachment(acceptedClasses={
 *     "Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationText",
 *     "Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationPresentation",
 *     "Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationSpeadsheet"
 * })
 */
class CmsFileDocument extends CmsFile
{
    use CmsFileMainAttachmentTrait;

    /**
     * @var AccessRightCmsFileDocument[]|ArrayCollection<AccessRightCmsFileDocument>
     *
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileDocument", mappedBy="cmsfiledocument")
     */
    protected $accessRights;

    public static function getCmsFileType()
    {
        return 'document';
    }
}
