<?php
/**
 * Created by mikaelp on 2018-10-17 5:24 PM
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;


use Azimut\Bundle\CmsBundle\Entity\CmsFile;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 * @ORM\Table(name="cms_cmsfile_title_and_text")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="title_and_text")
 */
class CmsFileTitleAndText extends CmsFile
{
    /**
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileTitleAndText", mappedBy="cmsfiletitle_and_text")
     */
    protected $accessRights;

    public function getName($locale = null)
    {
        return $this->getTitle($locale);
    }

    public static function getCmsFileType()
    {
        return 'title_and_text';
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     */
    public function getTitle($locale = null)
    {
        /** @var CmsFileTitleAndTextTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getTitle();
    }

    public function setTitle($title, $locale = null)
    {
        /** @var CmsFileTitleAndTextTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        $proxy->setTitle($title);

        return $this;
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     */
    public function getText($locale = null)
    {
        /** @var CmsFileTitleAndTextTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getText();
    }

    public function setText($text, $locale = null)
    {
        /** @var CmsFileTitleAndTextTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        $proxy->setText($text);

        return $this;
    }

    public function getAbstract($locale = null)
    {
        return $this->getText();
    }

    public static function getParentsClassesSecurityContextObject()
    {
        return Page::class;
    }

}
