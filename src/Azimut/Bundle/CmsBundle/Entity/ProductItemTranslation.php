<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-18 10:15:30
 */

namespace Azimut\Bundle\CmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

use Azimut\Bundle\ShopBundle\Entity\BaseProductItemTranslation;

/**
 * @ORM\Entity()
 * @ORM\Table(name="cms_product_item_translation")
 */
class ProductItemTranslation extends BaseProductItemTranslation
{
    /**
     * @ORM\ManyToOne(targetEntity="ProductItem", inversedBy="translations")
     * @ORM\JoinColumn(onDelete="CASCADE")
     */
    protected $productItem;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=true)
     * @Assert\NotNull()
     * @Assert\Length(max = 50)
     */
    protected $text;

    /**
     * Get translatable
     *
     * @return ProductItem
     */
    public function getTranslatable()
    {
        return $this->productItem;
    }

    /**
     * Set translatable
     *
     * @param ProductItem $translatable
     *
     * @return self
     */
    public function setTranslatable($translatable)
    {
        if (!$translatable instanceof ProductItem) {
            throw new \RuntimeException(sprintf('Expected $translatable to be an instance of "%s"', ProductItem::class));
        }
        $this->productItem = $translatable;
        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return self
     */
    public function setText($text)
    {
        $this->text = $text;
        return $this;
    }
}

