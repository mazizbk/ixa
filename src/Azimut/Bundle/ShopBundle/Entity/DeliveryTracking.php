<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-03-13 10:03:25
 */

namespace Azimut\Bundle\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Table(name="shop_order_delivery_tracking")
 * @ORM\Entity()
 */
class DeliveryTracking
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"detail_order"})
     */
    protected $id;

    /**
     * @var Order
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\ShopBundle\Entity\Order", inversedBy="deliveryTrackings")
     * @ORM\JoinColumn(name="order_id", referencedColumnName="id", onDelete="CASCADE", nullable=false)
     */
    protected $order;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255)
     * @Groups({"detail_order"})
     */
    protected $code;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=true)
     * @Groups({"detail_order"})
     */
    protected $label;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_delivered", type="boolean")
     * @Groups({"detail_order"})
     */
    protected $isDelivered = false;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="shipping_date", type="date", nullable=true)
     * @Groups({"detail_order"})
     */
    protected $shippingDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="delivery_date", type="date", nullable=true)
     * @Groups({"detail_order"})
     */
    protected $deliveryDate;

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
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }

    /**
     * Get code
     *
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set code
     *
     * @param string $code
     *
     * @return self
     */
    public function setCode($code)
    {
        $this->code = $code;
        return $this;
    }

    /**
     * Get label
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label
     *
     * @param string $label
     *
     * @return self
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * Get or set delivered flag
     *
     * @param bool|null $isDelivered
     *
     * @return bool|self
     */
    public function isDelivered($isDelivered = null)
    {
        if (null !== $isDelivered) {
            $this->isDelivered = $isDelivered;
            return $this;
        }

        return $this->isDelivered;
    }

    /**
     * Get shippingDate
     *
     * @return \DateTime
     */
    public function getShippingDate()
    {
        return $this->shippingDate;
    }

    /**
     * Set shippingDate
     *
     * @param \DateTime $shippingDate
     *
     * @return self
     */
    public function setShippingDate(\DateTime $shippingDate)
    {
        $this->shippingDate = $shippingDate;
        return $this;
    }

    /**
     * Get deliveryDate
     *
     * @return \DateTime
     */
    public function getDeliveryDate()
    {
        return $this->deliveryDate;
    }

    /**
     * Set deliveryDate
     *
     * @param \DateTime $deliveryDate
     *
     * @return self
     */
    public function setDeliveryDate(\DateTime $deliveryDate)
    {
        $this->deliveryDate = $deliveryDate;
        return $this;
    }
}
