<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-05 12:25:06
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\MediacenterBundle\Entity\AbstractMediaDeclinationAttachment;

/**
 * @ORM\Entity
 * @ORM\Table(name="cms_cmsfile_media_declination_attachment")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="cmsfile")
 */
class CmsFileMediaDeclinationAttachment extends AbstractMediaDeclinationAttachment
{
    /**
     * @var CmsFile
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFile")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $cmsFile;

    /**
     * Get cmsFile
     *
     * @return CmsFile
     */
    public function getCmsFile()
    {
        return $this->cmsFile;
    }

    /**
     * Set cmsFile
     *
     * @param CmsFile $cmsFile
     *
     * @return self
     */
    public function setCmsFile($cmsFile)
    {
        $this->cmsFile = $cmsFile;
        return $this;
    }

    /**
     * Get object to wich MediaDeclination is attached
     *
     * @return CmsFile
     */
    public function getAttachedObject()
    {
        return $this->cmsFile;
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachedObjectTypeName()
    {
        if (null != $this->cmsFile) {
            return 'cms.file.type.' . $this->cmsFile->getCmsFileType();
        }

        return 'cms.file';
    }

    /**
     * {@inheritdoc}
     */
    public function getAttachedObjectName()
    {
        if (null != $this->cmsFile) {
            return $this->cmsFile->getName();
        }

        return null;
    }
}
