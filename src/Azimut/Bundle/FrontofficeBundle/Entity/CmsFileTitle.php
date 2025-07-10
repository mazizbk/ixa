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
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 * @ORM\Table(name="cms_cmsfile_title")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="title")
 */
class CmsFileTitle extends CmsFile
{
    /**
     * @var AccessRightCmsFileTitle[]|ArrayCollection<AccessRightCmsFileTitle>
     *
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileTitle", mappedBy="cmsfiletitle")
     */
    protected $accessRights;

    public function getName($locale = null)
    {
        return $this->getTitle($locale);
    }

    public static function getCmsFileType()
    {
        return 'title';
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     */
    public function getTitle($locale = null)
    {
        /** @var CmsFileTitleTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getTitle();
    }

    public function setTitle($title, $locale = null)
    {
        /** @var CmsFileTitleTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setTitle($title);

        return $this;
    }

    public function getAbstract($locale = null)
    {
        return $this->getTitle($locale);
    }
}
