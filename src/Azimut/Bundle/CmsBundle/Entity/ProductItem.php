<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-18 10:15:30
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;

use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;
use Azimut\Bundle\ShopBundle\Entity\BaseProductItem;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cms_product_item")
 */
class ProductItem extends BaseProductItem
{
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="ProductItemTranslation", mappedBy="productItem", cascade={"persist", "remove"}, orphanRemoval=true, indexBy="locale")
     * @Assert\Valid()
     */
    protected $translations;

    /**
     * @var CmsFile
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\CmsBundle\Entity\CmsFileProduct", inversedBy="productItems")
     * @ORM\JoinColumn(name="cms_file_product_id", referencedColumnName="id", nullable=false, onDelete="cascade")
     */
    protected $cmsFile;

    /**
     * Get translation class
     *
     * @return string
     */
    static function getTranslationClass()
    {
        return ProductItemTranslation::class;
    }

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
    public function setCmsFile(CmsFile $cmsFile)
    {
        if (null != $this->cmsFile) {
            $this->cmsFile->removeProductItem($this);
        }

        $this->cmsFile = $cmsFile;

        if (null != $cmsFile) {
            $cmsFile->addProductItem($this);
        }

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     * @VirtualProperty()
     * @Groups({"list_product_items", "detail_product_item"})
     */
    public function getText($locale = null)
    {
        $proxy = new TranslationProxy($this, $locale);
        return $proxy->getText();
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return self
     */
    public function setText($text, $locale = null)
    {
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setText($text);
        return $this;
    }

    // Use this method if product item does not own price but parent cmsFile does
    // If not, comment or REMOVE THIS method (and let BaseProductItem do the job)
    /**
     * Get price
     *
     * @return int|null
     */
    public function getPrice()
    {
        return (null != $this->cmsFile) ? $this->cmsFile->getPrice() : null;
    }
}
