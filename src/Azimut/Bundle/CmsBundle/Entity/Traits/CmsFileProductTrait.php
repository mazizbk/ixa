<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-18 10:55:31
 */

namespace Azimut\Bundle\CmsBundle\Entity\Traits;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use Azimut\Bundle\CmsBundle\Entity\ProductItem;

trait CmsFileProductTrait
{
    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="Azimut\Bundle\CmsBundle\Entity\ProductItem", mappedBy="cmsFile", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    protected $productItems;

    public function __construct()
    {
        $this->productItems = new ArrayCollection();
    }

    /**
     * Get productItems
     *
     * @return ArrayCollection
     */
    public function getProductItems()
    {
        return $this->productItems;
    }

    /**
     * Set productItems
     *
     * @param ArrayCollection $productItems
     *
     * @return self
     */
    public function setProductItems(ArrayCollection $productItems)
    {
        foreach ($productItems as $productItem) {
            $this->addDeliveryTracking($productItem);
        }
        return $this;
    }

    /**
     * Add productItem
     *
     * @param ProductItem $productItem
     *
     * @return self
     */
    public function addProductItem(ProductItem $productItem)
    {
        if (!$this->productItems->contains($productItem)) {
            $this->productItems->add($productItem);
            if ($productItem->getCmsFile() != $this) {
                $productItem->setCmsFile($this);
            }
        }
        return $this;
    }

    /**
     * Remove productItem
     *
     * @param ProductItem $productItem
     *
     * @return self
     */
    public function removeProductItem($productItem)
    {
        if ($this->productItems->contains($productItem)) {
            $this->productItems->removeElement($productItem);
        }
        return $this;
    }
}
