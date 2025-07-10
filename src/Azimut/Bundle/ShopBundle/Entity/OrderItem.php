<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-14 15:52:58
 */

namespace Azimut\Bundle\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Symfony\Component\Validator\Constraints as Assert;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Doctrine\Common\Collections\ArrayCollection;

use Azimut\Bundle\DoctrineExtraBundle\Entity\TranslatableEntityInterface;
use Azimut\Bundle\DoctrineExtraBundle\Translation\TranslationProxy;

/**
 * @ORM\Table(name="shop_order_item")
 * @ORM\Entity()
 */
class OrderItem implements TranslatableEntityInterface
{
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"detail_order"})
     */
    private $id;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="OrderItemTranslation", mappedBy="orderItem", cascade={"persist", "remove"}, orphanRemoval=true, indexBy="locale")
     * @Assert\Valid()
     */
    protected $translations;

    /**
     * @var int
     *
     * @ORM\Column(name="quantity", type="integer", options={"unsigned"=true})
     * @Groups({"detail_order"})
     */
    protected $quantity = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="price", type="integer")
     * @Groups({"detail_order"})
     */
    protected $price;

    /**
     * @var int
     *
     * @ORM\Column(name="vat_rate", type="integer", nullable=true)
     * @Groups({"detail_order"})
     */
    protected $vatRate;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\ShopBundle\Entity\Order", inversedBy="orderItems")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="product_item_class", type="string", length=255, nullable=true)
     */
    protected $productItemClass;

    /**
     * @var integer
     *
     * @ORM\Column(name="product_item_id", type="integer", nullable=true)
     */
    protected $productItemId;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_deletable", type="boolean")
     */
    protected $isDeletable = true;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    static function getTranslationClass()
    {
        return OrderItemTranslation::class;
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * Get name
     *
     * @param string|null $locale
     *
     * @return string
     * @VirtualProperty()
     * @Groups({"detail_order"})
     */
    public function getName($locale = null)
    {
        $proxy = new TranslationProxy($this, $locale);
        return $proxy->getName();
    }

    /**
     * Set name
     *
     * @param string $name
     * @param string|null $locale
     *
     * @return self
     */
    public function setName($name, $locale = null)
    {
        $proxy = new TranslationProxy($this, $locale);
        $proxy->setName($name);
        return $this;
    }

    /**
     * Get quantity
     *
     * @return int
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set quantity
     *
     * @param integer $quantity
     *
     * @return self
     */
    public function setQuantity($quantity)
    {
        if ($this->price < 0) {
            throw new \Exception("Cannot change quantity on a discount order item");
        }
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Get price
     *
     * @return int
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set price
     *
     * @param integer $price
     *
     * @return self
     */
    public function setPrice($price)
    {
        $this->price = $price;
        return $this;
    }

    /**
     * Get vat rate
     *
     * @return int|null
     */
    public function getVatRate()
    {
        return $this->vatRate;
    }

    /**
     * Set vat rate
     *
     * @param int|null $vatRate
     *
     * @return self
     */
    public function setVatRate($vatRate)
    {
        $this->vatRate = $vatRate;
        return $this;
    }

    /**
     * Return the pre tax price (en français prix hors taxes)
     * @param  int $defaultVatRate Default VAT rate
     * @return int
     */
    public function getPreTaxPrice($defaultVatRate)
    {
        $vatRate = $defaultVatRate * 10;
        if (null != $this->vatRate) {
            $vatRate = $this->vatRate;
        }
        return (int) round($this->price / (1 + $vatRate/1000));
    }

    /**
     * Get order
     *
     * @return Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set order
     *
     * @param Order $order
     *
     * @return self
     */
    public function setOrder(Order $order)
    {
        if (null != $this->order) {
            $this->order->removeOrderItem($this);
        }

        $this->order = $order;

        if (null != $order) {
            $order->addOrderItem($this);
        }

        return $this;
    }

    /**
     * Get productItemClass
     *
     * @return string
     */
    public function getProductItemClass()
    {
        return $this->productItemClass;
    }

    /**
     * Set productItemClass
     *
     * @param string $productItemClass
     *
     * @return self
     */
    public function setProductItemClass($productItemClass)
    {
        $this->productItemClass = $productItemClass;
        return $this;
    }

    /**
     * Get productItemId
     *
     * @return integer
     */
    public function getProductItemId()
    {
        return $this->productItemId;
    }

    /**
     * Set productItemId
     *
     * @param integer $productItemId
     *
     * @return self
     */
    public function setProductItemId($productItemId)
    {
        $this->productItemId = $productItemId;
        return $this;
    }

    /**
     * Create OrderItem from ProductItem
     *
     * @param  BaseProductItem $productItem
     * @return OrderItem
     */
    static function createFromProductItem(BaseProductItem $productItem)
    {
        $orderItem = new OrderItem();

        foreach ($productItem->getTranslations() as $productItemTranslation) {
            $orderItem->setName($productItemTranslation->getName(), $productItemTranslation->getLocale());
        }

        $orderItem
            ->setPrice($productItem->getPrice())
            ->setVatRate($productItem->getVatRate())
            ->setProductItemClass(get_class($productItem))
            ->setProductItemId($productItem->getId())
        ;

        return $orderItem;
    }

    /**
     * Get or set isDeletable
     *
     * @param boolean|null
     *
     * @return boolean
     */
    public function isDeletable($isDeletable = null)
    {
        if (null !== $isDeletable) {
            $this->isDeletable = $isDeletable;
            return $this;
        }

        return $this->isDeletable;
    }
}
