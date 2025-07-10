<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-03-13 11:02:06
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
 * @ORM\Table(name="cms_cmsfile_rich_text")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="rich_text")
 */
class CmsFileRichText extends CmsFile
{
    /**
     * @var AccessRightCmsFileRichText[]|ArrayCollection<AccessRightCmsFileRichText>
     *
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileRichText", mappedBy="cmsfilerich_text")
     */
    protected $accessRights;

    public static function getCmsFileType()
    {
        return 'rich_text';
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     */
    public function getText($locale = null)
    {
        /** @var CmsFileRichTextTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getText();
    }

    public function getPlainText($locale = null)
    {
        return strip_tags($this->getText($locale));
    }

    public function setText($text, $locale = null)
    {
        /** @var CmsFileRichTextTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setText($text);

        return $this;
    }

    public function getAbstract($locale = null)
    {
        // NB: do not cut content length here, do it in template after stripping media declination tags
        return html_entity_decode($this->getPlainText($locale));
    }

    public static function getParentsClassesSecurityContextObject()
    {
        return Page::class;
    }
}
