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
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileRelatedArticlesTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileSeoMetaTrait;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 * @ORM\Table(name="cms_cmsfile_article")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="article")
 * @AzimutAssert\LangFilled(requiredFields={"title","text"})
 * @AzimutCmsAssert\HasValidMainAttachment(acceptedClasses={
 *     "Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationImage",
 *     "Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationVideo"
 * })
 * @AzimutCmsAssert\HasValidSecondaryAttachments(acceptedClasses={
 *     "Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationImage",
 *     "Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationVideo",
 *     "Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationText"
 * })
 */
class CmsFileArticle extends CmsFile
{
    use CmsFileMainAttachmentTrait;
    use CmsFileSecondaryAttachmentsTrait {
        CmsFileSecondaryAttachmentsTrait::__construct as private __constructCmsFileSecondaryAttachmentsTrait;
    }
    use CmsFileCommentTrait {
        CmsFileCommentTrait::__construct as private __constructCmsFileCommentTrait;
    }
    use CmsFileRelatedArticlesTrait {
        CmsFileRelatedArticlesTrait::__construct as private __constructCmsFileRelatedArticlesTrait;
    }
    use CmsFileSeoMetaTrait;

    protected static $allowPublicApi = true;

    /**
     * @var AccessRightCmsFileArticle[]|ArrayCollection<AccessRightCmsFileArticle>
     *
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileArticle", mappedBy="cmsfilearticle")
     */
    protected $accessRights;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=150, nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $author;

    public function __construct()
    {
        parent::__construct();
        $this->__constructCmsFileSecondaryAttachmentsTrait();
        $this->__constructCmsFileCommentTrait();
        $this->__constructCmsFileRelatedArticlesTrait();
    }

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
        return 'article';
    }

    /**
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     * @param string $locale
     * @return string
     */
    public function getTitle($locale = null)
    {
        /** @var CmsFileArticleTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getTitle();
    }

    public function setTitle($title, $locale = null)
    {
        /** @var CmsFileArticleTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setTitle($title);

        return $this;
    }

    public function setAuthor($author)
    {
        $this->author = $author;

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
        /** @var CmsFileArticleTranslation $proxy */
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
        /** @var CmsFileArticleTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setText($text);

        return $this;
    }

    public function getAuthor()
    {
        return $this->author;
    }
}
