<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-11-02 15:25:00
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
 * @ORM\Table(name="cms_cmsfile_video")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="video")
 * @AzimutCmsAssert\HasValidMainAttachment(acceptedClasses={
 *     "Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationVideo"
 * })
 */
class CmsFileVideo extends CmsFile
{
    use CmsFileMainAttachmentTrait;

    /**
     * @var AccessRightCmsFileVideo[]|ArrayCollection<AccessRightCmsFileVideo>
     *
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileVideo", mappedBy="cmsfilevideo")
     */
    protected $accessRights;

    public static function getCmsFileType()
    {
        return 'video';
    }
}
