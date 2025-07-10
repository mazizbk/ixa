<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-05 10:57:46
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints as AzimutAssert;
use Azimut\Bundle\CmsBundle\Validator\Constraints as AzimutCmsAssert;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileMainAttachmentTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileSecondaryAttachmentsTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileCommentTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileRelatedBlocksTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileSeoMetaTrait;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 * @ORM\Table(name="cms_cmsfile_block")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="block")
 * @AzimutAssert\LangFilled(requiredFields={"title"})
 * @AzimutCmsAssert\HasValidMainAttachment(acceptedClasses={
 *     "Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationImage",
 * })
 */
class CmsFileBlock extends CmsFile
{
    use CmsFileMainAttachmentTrait;

    protected static $allowPublicApi = true;

    /**
     * @var AccessRightCmsFileBlock[]|ArrayCollection<AccessRightCmsFileBlock>
     *
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileBlock", mappedBy="cmsfileblock")
     */
    protected $accessRights;

    public function getName($locale = null)
    {
        return $this->getTitle($locale);
    }

    public function getThumb()
    {
        $mainAttachment = $this->getMainAttachment();
        (null != $mainAttachment)?$mainAttachment->getThumb():null;
    }

    public static function getCmsFileType()
    {
        return 'block';
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     * @param string $locale
     * @return string
     */
    public function getTitle($locale = null)
    {
        /** @var CmsFileBlockTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getTitle();
    }

    public function setTitle($title, $locale = null)
    {
        /** @var CmsFileBlockTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setTitle($title);

        return $this;
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     * @param string $locale
     * @return string
     */
    public function getText($locale = null)
    {
        /** @var CmsFileBlockTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getText();
    }

    public function getPlainText($locale = null)
    {
        $text = $this->getText($locale);
        if (is_array($text)) {
            foreach ($text as $locale => $translatedText) {
                $text[$locale] = strip_tags(html_entity_decode($translatedText));
            }
            return $text;
        }
        return strip_tags(html_entity_decode($text));
    }

    public function getAbstract($locale = null)
    {
        $text = $this->getPlainText($locale);

        // NB: do not cut content length here, do it in template after stripping media declination tags

        return $text;
    }

    public function setText($text, $locale = null)
    {
        /** @var CmsFileBlockTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setText($text);

        return $this;
    }
}
