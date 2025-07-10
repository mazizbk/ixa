<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-10-01 10:59:10
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\DoctrineExtraBundle\Validator\Constraints as AzimutAssert;
use Azimut\Bundle\CmsBundle\Validator\Constraints as AzimutCmsAssert;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileMainAttachmentTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileSecondaryAttachmentsTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment1Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment2Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment3Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileComplementaryAttachment4Trait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileProductTrait;
use Azimut\Bundle\CmsBundle\Entity\Traits\CmsFileCommentTrait;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\CmsBundle\Entity\Repository\CmsFileRepository")
 * @ORM\Table(name="cms_cmsfile_product")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="product")
 * @AzimutAssert\LangFilled(requiredFields={"title","text"})
 * @AzimutCmsAssert\HasValidMainAttachment(acceptedClasses={
 *     "Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationImage",
 *     "Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationVideo"
 * })
 * @AzimutCmsAssert\HasValidComplementaryAttachment1(acceptedClasses={"Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationImage"})
 * @AzimutCmsAssert\HasValidComplementaryAttachment2(acceptedClasses={"Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationImage"})
 * @AzimutCmsAssert\HasValidComplementaryAttachment3(acceptedClasses={"Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationImage"})
 * @AzimutCmsAssert\HasValidComplementaryAttachment4(acceptedClasses={"Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationImage"})
 */
class CmsFileProduct extends CmsFile
{
    use CmsFileMainAttachmentTrait;
    use CmsFileComplementaryAttachment1Trait, CmsFileComplementaryAttachment2Trait, CmsFileComplementaryAttachment3Trait, CmsFileComplementaryAttachment4Trait;
    use CmsFileSecondaryAttachmentsTrait {
        CmsFileSecondaryAttachmentsTrait::__construct as private __constructCmsFileSecondaryAttachmentsTrait;
    }
    use CmsFileProductTrait {
        CmsFileProductTrait::__construct as private __constructCmsFileProductTrait;
    }
    use CmsFileCommentTrait {
        CmsFileCommentTrait::__construct as private __constructCmsFileCommentTrait;
    }

    /**
     * @var bool
     */
    protected static $allowPublicApi = true;

    /**
     * @var AccessRightCmsFileProduct[]|ArrayCollection<AccessRightCmsFileProduct>
     *
     * @ORM\OneToMany(targetEntity="AccessRightCmsFileProduct", mappedBy="cmsfileproduct")
     */
    protected $accessRights;

    /**
     * @var int
     *
     * @ORM\Column(type="integer", nullable=true)
     * @Groups({"detail_cms_file"})
     */
    protected $price;

    /**
     * Unidirectional One-To-Many
     * @ORM\ManyToMany(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileProduct")
     * @ORM\JoinTable(name="cms_file_product_associated_product", inverseJoinColumns={@ORM\JoinColumn(name="associated_cms_file_product_id", unique=true)})
     * @Groups({"detail_cms_file"})
     */
    private $associatedProducts;

    public function __construct()
    {
        parent::__construct();
        $this->__constructCmsFileSecondaryAttachmentsTrait();
        $this->associatedProducts = new ArrayCollection();
        $this->__constructCmsFileProductTrait();
    }

    public static function getComplementaryAttachment1Label()
    {
        return 'image.lorem';
    }

    public static function getComplementaryAttachment2Label()
    {
        return 'image.ipsum';
    }

    public static function getComplementaryAttachment3Label()
    {
        return 'image.epsum';
    }

    public static function getComplementaryAttachment4Label()
    {
        return 'image.amet';
    }

    /**
     * Get name
     *
     * @param string|null $locale
     *
     * @return string
     */
    public function getName($locale = null)
    {
        return $this->getTitle($locale);
    }

    /**
     * Get image thumbnail
     *
     * @return string|null
     */
    public function getThumb()
    {
        return (null != $this->getMainAttachment())?$this->getMainAttachment()->getThumb():null;
    }

    /**
     * Get CmsFile type name
     *
     * @return string
     */
    public static function getCmsFileType()
    {
        return 'product';
    }

    /**
     * Get title
     *
     * @param string|null $locale
     *
     * @return string
     *
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     * @param string $locale
     * @return string
     */
    public function getTitle($locale = null)
    {
        /** @var CmsFileProductTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getTitle();
    }

    /**
     * Set title
     *
     * @param string $title
     * @param string|null $locale
     *
     * @return self
     */
    public function setTitle($title, $locale = null)
    {
        /** @var CmsFileProductTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setTitle($title);

        return $this;
    }

    /**
     * Get subtitle
     *
     * @param string|null $locale
     *
     * @return string
     *
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     * @param string $locale
     * @return string
     */
    public function getSubtitle($locale = null)
    {
        /** @var CmsFileProductTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getSubtitle();
    }

    /**
     * Set subtitle
     *
     * @param string $subtitle
     * @param string|null $locale
     *
     * @return self
     */
    public function setSubtitle($subtitle, $locale = null)
    {
        /** @var CmsFileProductTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setSubtitle($subtitle);

        return $this;
    }

    /**
     * Get text
     *
     * @param string|null $locale
     *
     * @return string
     *
     * @VirtualProperty()
     * @Groups({"detail_cms_file"})
     * @param string $locale
     * @return string
     */
    public function getText($locale = null)
    {
        /** @var CmsFileProductTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);

        return $proxy->getText();
    }

    /**
     * Get plain text
     *
     * @param string|null $locale
     *
     * @return string
     */
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

    /**
     * Get abstract
     *
     * @param string|null $locale
     *
     * @return string
     */
    public function getAbstract($locale = null)
    {
        $text = $this->getPlainText($locale);

        return $text;
    }

    /**
     * Set text
     *
     * @param string $text
     * @param string|null $locale
     *
     * @return self
     */
    public function setText($text, $locale = null)
    {
        /** @var CmsFileProductTranslation $proxy */
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setText($text);

        return $this;
    }

    /**
     * Get price
     *
     * @return int|null
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param int|null $price
     *
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get associated products
     *
     * @return ArrayCollection
     */
    public function getAssociatedProducts()
    {
        return $this->associatedProducts;
    }

    /**
     * Set associated products
     *
     * @param ArrayCollection|array $associatedProducts
     *
     * @return self
     */
    public function setAssociatedProducts($associatedProducts)
    {
        $this->associatedProducts = $associatedProducts;
        return $this;
    }
}
