<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-07-28
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileMainAttachmentTrait;
use Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints as AzimutAssert;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 * @ORM\Table(name="cms_cmsfile_press_review")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="press_review")
 * @AzimutAssert\LangFilled(requiredFields={"title","text"})
 */
class CmsFilePressReview extends CmsFile
{
    use CmsFileMainAttachmentTrait;

    /**
     * @var AccessRightCmsFilePressReview[]|ArrayCollection<AccessRightCmsFilePressReview>
     * @ORM\OneToMany(targetEntity="AccessRightCmsFilePressReview", mappedBy="cmsfilepress_review")
     */
    protected $accessRights;

    public function getName($locale = null)
    {
        return $this->getTitle($locale);
    }

    public static function getCmsFileType()
    {
        return 'press_review';
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     * @param string $locale
     * @return string
     */
    public function getTitle($locale = null)
    {
        /** @var CmsFilePressReviewTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getTitle();
    }

    public function setTitle($title, $locale = null)
    {
        /** @var CmsFilePressReviewTranslation $proxy */
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
        /** @var CmsFilePressReviewTranslation $proxy */
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
        /** @var CmsFilePressReviewTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setText($text);

        return $this;
    }
}
