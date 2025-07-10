<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-14 15:08:46
 */

namespace Azimut\Bundle\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\VirtualProperty;
use Doctrine\Common\Collections\ArrayCollection;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\Validator\Constraints as Assert;

use Azimut\Bundle\SecurityBundle\Security\ObjectAccessRightAware;
use Azimut\Bundle\ShopBundle\Entity\AccessRightOrder;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Azimut\Bundle\ShopBundle\Service\Delivery\DeliveryProviderInterface;
use Azimut\Bundle\ShopBundle\Service\Payment\PaymentProviderInterface;
use Azimut\Bundle\ShopBundle\Service\OrderStatusProvider;

/**
 * @ORM\Table(name="shop_order")
 * @ORM\Entity(repositoryClass="Azimut\Bundle\ShopBundle\Entity\Repository\OrderRepository")
 */
class Order
{
    use ObjectAccessRightAware;
    use TimestampableEntity;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"list_orders", "detail_order", "security_access_right_obj"})
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="number", type="string", length=11, nullable=true)
     * @Groups({"list_orders", "detail_order"})
     */
    protected $number;

    /**
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeBundle\Entity\Site")
     * @ORM\JoinColumn(name="site", referencedColumnName="id", onDelete="SET NULL")
     */
    protected $site;

    /**
     * @var FrontofficeUser
     *
     * @ORM\ManyToOne(targetEntity="Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser")
     * @ORM\JoinColumn(name="user", referencedColumnName="id")
     */
    protected $user;

    /**
     * @ORM\Column(name="status", type="integer", options={"unsigned"=true}, nullable=true)
     * @Groups({"list_orders", "detail_order"})
     */
    protected $status;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="order_date", type="date", nullable=true)
     * @Groups({"list_orders", "detail_order"})
     */
    protected $orderDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="processing_date", type="date", nullable=true)
     * @Groups({"list_orders", "detail_order"})
     */
    protected $processingDate;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="closing_date", type="date", nullable=true)
     * @Groups({"list_orders", "detail_order"})
     */
    protected $closingDate;

    /**
     * @var ArrayCollection
     *
     * @ORM\OneToMany(targetEntity="DeliveryTracking", mappedBy="order", cascade={"persist"})
     * @Groups({"detail_order"})
     */
    protected $deliveryTrackings;

    /**
     * @var string
     *
     * @ORM\Column(name="client_comment", type="text", length=255, nullable=true)
     * @Groups({"detail_order"})
     */
    protected $clientComment;

    /**
     * @var string
     *
     * @ORM\Column(name="private_comment", type="text", length=255, nullable=true)
     * @Groups({"detail_order"})
     */
    protected $privateComment;

    /**
     * @var int
     *
     * @ORM\Column(name="shipping_cost", type="integer", nullable=true)
     * @Groups({"detail_order"})
     */
    protected $shippingCost;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_provider_id", type="string", length=255, nullable=true)
     */
    protected $deliveryProviderId;

    /**
     * @var string
     *
     * @ORM\Column(name="delivery_provider_name", type="string", length=255, nullable=true)
     * @Groups({"detail_order"})
     */
    protected $deliveryProviderName;

    /**
     * @var OrderAddress
     *
     * @ORM\OneToOne(targetEntity="OrderAddress", cascade={"persist", "remove"})
     * @Assert\Valid()
     * @Groups({"list_orders", "detail_order"})
     */
    protected $deliveryAddress;

    /**
     * @var OrderAddress
     *
     * @ORM\OneToOne(targetEntity="OrderAddress", cascade={"persist", "remove"})
     * @Assert\Valid()
     * @Groups({"list_orders", "detail_order"})
     */
    protected $billingAddress;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_provider_id", type="string", length=255, nullable=true)
     */
    protected $paymentProviderId;

    /**
     * @var string
     *
     * @ORM\Column(name="payment_provider_name", type="string", length=255, nullable=true)
     * @Groups({"detail_order"})
     */
    protected $paymentProviderName;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="payment_date", type="date", nullable=true)
     * @Groups({"list_orders", "detail_order"})
     */
    protected $paymentDate;

    /**
     * @var int
     *
     * @ORM\Column(name="total_amount", type="integer", nullable=true)
     * @Groups({"list_orders", "detail_order"})
     */
    protected $totalAmount;

    /**
     * @var int
     *
     * @ORM\Column(name="total_pre_tax_amount", type="integer", nullable=true)
     * @Groups({"list_orders", "detail_order"})
     */
    protected $totalPreTaxAmount;

    /**
     * @ORM\OneToMany(targetEntity="OrderItem", mappedBy="order", cascade={"persist"})
     * @Groups({"detail_order"})
     */
    protected $orderItems;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=2)
     */
    protected $locale;

    /**
     * @var PaymentProviderConfiguration
     *
     * @ORM\OneToOne(targetEntity="PaymentProviderConfiguration", cascade={"persist", "remove"})))
     */
    protected $paymentProviderConfiguration;

    public function __construct()
    {
        $this->orderItems = new ArrayCollection();
        $this->deliveryTrackings = new ArrayCollection();
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
     * Get number
     *
     * @return string|null
     */
    public function getNumber()
    {
        return $this->number;
    }

    /**
     * Set number
     *
     * @param string|null $number
     *
     * @return self
     */
    public function setNumber($number)
    {
        $this->number = $number;
        return $this;
    }

    /**
     * Get site
     *
     * @return Site
     */
    public function getSite()
    {
        return $this->site;
    }

    /**
     * Set site
     *
     * @param Site $site
     *
     * @return self
     */
    public function setSite($site)
    {
        $this->site = $site;
        return $this;
    }

    /**
     * Get user
     *
     * @return FrontofficeUser
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set user
     *
     * @param FrontofficeUser $user
     *
     * @return self
     */
    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * Get status
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * Set status
     *
     * @param integer $status
     *
     * @return self
     */
    public function setStatus($status)
    {
        $this->status = $status;

        // If status reseted
        if (null == $status) {
            $this->setTotalAmount(null);
            $this->setTotalPretaxAmount(null);
        }

        return $this;
    }

    /**
     * Get orderDate
     *
     * @return \DateTime|null
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * Set orderDate
     *
     * @param \DateTime|null $orderDate
     *
     * @return self
     */
    public function setOrderDate($orderDate)
    {
        $this->orderDate = $orderDate;
        return $this;
    }

    /**
     * Get processingDate
     *
     * @return \DateTime|null
     */
    public function getProcessingDate()
    {
        return $this->processingDate;
    }

    /**
     * Set processingDate
     *
     * @param \DateTime|null $processingDate
     *
     * @return self
     */
    public function setProcessingDate($processingDate)
    {
        $this->processingDate = $processingDate;
        return $this;
    }

    /**
     * Get closingDate
     *
     * @return \DateTime|null
     */
    public function getClosingDate()
    {
        return $this->closingDate;
    }

    /**
     * Set closingDate
     *
     * @param \DateTime|null $closingDate
     *
     * @return self
     */
    public function setClosingDate($closingDate)
    {
        $this->closingDate = $closingDate;
        return $this;
    }

    /**
     * Get clientComment
     *
     * @return string
     */
    public function getClientComment()
    {
        return $this->clientComment;
    }

    /**
     * Set clientComment
     *
     * @param string $clientComment
     *
     * @return self
     */
    public function setClientComment($clientComment)
    {
        $this->clientComment = $clientComment;
        return $this;
    }

    /**
     * Get privateComment
     *
     * @return string
     */
    public function getPrivateComment()
    {
        return $this->privateComment;
    }

    /**
     * Set privateComment
     *
     * @param string $privateComment
     *
     * @return self
     */
    public function setPrivateComment($privateComment)
    {
        $this->privateComment = $privateComment;
        return $this;
    }


    /**
     * Get shippingCost
     *
     * @return interger
     */
    public function getShippingCost()
    {
        return $this->shippingCost;
    }

    /**
     * Set shippingCost
     *
     * @param interger $shippingCost
     *
     * @return self
     */
    public function setShippingCost($shippingCost)
    {
        $this->shippingCost = $shippingCost;
        return $this;
    }

    /**
     * Get deliveryProviderId
     *
     * @return int|null
     */
    public function getDeliveryProviderId()
    {
        return $this->deliveryProviderId;
    }

    /**
     * Set deliveryProviderId
     *
     * @param int|null $deliveryProviderId
     *
     * @return self
     */
    public function setDeliveryProviderId($deliveryProviderId)
    {
        $this->deliveryProviderId = $deliveryProviderId;
        return $this;
    }

    /**
     * Get deliveryProviderName
     *
     * @return
     */
    public function getDeliveryProviderName()
    {
        return $this->deliveryProviderName;
    }

    /**
     * Get deliveryAddress
     *
     * @return OrderAddress|null
     */
    public function getDeliveryAddress()
    {
        return $this->deliveryAddress;
    }

    /**
     * Set deliveryAddress
     *
     * @param OrderAddress|null $deliveryAddress
     *
     * @return self
     */
    public function setDeliveryAddress($deliveryAddress)
    {
        $this->deliveryAddress = $deliveryAddress;
        return $this;
    }

    /**
     * Get billingAddress
     *
     * @return OrderAddress|null
     */
    public function getBillingAddress()
    {
        return $this->billingAddress;
    }

    /**
     * Set billingAddress
     *
     * @param OrderAddress|null $billingAddress
     *
     * @return self
     */
    public function setBillingAddress($billingAddress)
    {
        $this->billingAddress = $billingAddress;
        return $this;
    }

    /**
     * Get paymentProviderId
     *
     * @return PaymentProviderInterface|null
     */
    public function getPaymentProviderId()
    {
        return $this->paymentProviderId;
    }

    /**
     * Get paymentProviderName
     *
     * @return
     */
    public function getPaymentProviderName()
    {
        return $this->paymentProviderName;
    }

    /**
     * Get total item amount
     *
     * @return int
     */
    public function getTotalItemsAmount()
    {
        $totalAmount = 0;
        foreach ($this->orderItems as $orderItem) {
            $totalAmount += $orderItem->getPrice() * $orderItem->getQuantity();
        }
        return $totalAmount;
    }

    /**
     * Get totalAmount
     *
     * @return int|null
     */
    public function getTotalAmount()
    {
        if (null == $this->totalAmount) {
            return $this->getTotalItemsAmount() + $this->shippingCost;
        }
        return $this->totalAmount;
    }

    /**
     * Set totalAmount
     *
     * @param integer|null $totalAmount
     *
     * @return self
     */
    public function setTotalAmount($totalAmount)
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    /**
     * Get orderItems
     *
     * @return ArrayCollection
     */
    public function getOrderItems()
    {
        return $this->orderItems;
    }

    /**
     * Set orderItems
     *
     * @param ArrayCollection $orderItems
     *
     * @return self
     */
    public function setOrderItems(ArrayCollection $orderItems)
    {
        $this->orderItems = $orderItems;
        return $this;
    }

    /**
     * Add orderItem
     *
     * @param OrderItem $orderItem
     *
     * @return self
     */
    public function addOrderItem(OrderItem $orderItem)
    {
        if (!$this->orderItems->contains($orderItem)) {
            $this->orderItems->add($orderItem);
            if ($orderItem->getOrder() != $this) {
                $orderItem->setOrder($this);
            }
        }
        return $this;
    }

    /**
     * Remove orderItem
     *
     * @param OrderItem $orderItem
     *
     * @return self
     */
    public function removeOrderItem($orderItem)
    {
        if ($this->orderItems->contains($orderItem)) {
            $this->orderItems->removeElement($orderItem);
        }
        return $this;
    }

    /**
     * To string
     *
     * @return string
     *
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function __toString()
    {
        return $this->getNumber();
    }

    /**
     * Get parents security context object (used by FrontofficeVoter)
     *
     * @return null
     */
    public function getParentsSecurityContextObject()
    {
        return null;
    }

    /**
     * Get access right type
     *
     * @return string
     *
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public static function getAccessRightType()
    {
        return 'order';
    }

    /**
     * Get access right class name
     *
     * @return string
     */
    public static function getAccessRightClassName()
    {
        return AccessRightOrder::class;
    }

    /**
     * Get children security context objects
     *
     * @return array
     *
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function getChildrenSecurityContextObject()
    {
        return [];
    }

    /**
     * Get parent classes security context object
     *
     * @return null
     *
     * Used for SecurityVoter to determine the access rights class.
     */
    public static function getParentsClassesSecurityContextObject()
    {
        return null;
    }

    /**
     * Get children classes security context object
     *
     * @return array
     */
    public static function getChildrenClassesSecurityContextObject()
    {
        return [];
    }

    /**
     * Count product items
     *
     * @return integer Number of products in order
     */
    public function count()
    {
        $count = 0;
        foreach($this->orderItems as $orderItem) {
            $count += $orderItem->getQuantity();
        }
        return $count;
    }

    /**
     * Set delivery provider and calculate shopping cost
     *
     * @param  DeliveryProviderInterface $deliveryProvider
     * @return self
     */
    public function setDeliveryProvider(DeliveryProviderInterface $deliveryProvider)
    {
        $this->deliveryProviderId = $deliveryProvider->getId();
        $this->deliveryProviderName = $deliveryProvider->getName();
        $this->shippingCost = $deliveryProvider->getShippingCost($this);

        return $this;
    }

    /**
     * Set payment provider and create order number
     *
     * @param  PaymentProviderInterface $paymentProvider
     * @return self
     */
    public function setPaymentProvider(PaymentProviderInterface $paymentProvider)
    {
        $this->paymentProviderId = $paymentProvider->getId();
        $this->paymentProviderName = $paymentProvider->getName();

        $this->setStatus(OrderStatusProvider::STATUS_VALIDATED);

        return $this;
    }

    /**
     * Get paymentProviderConfiguration
     *
     * @return PaymentProviderConfiguration
     */
    public function getPaymentProviderConfiguration()
    {
        return $this->paymentProviderConfiguration;
    }

    /**
     * Set paymentProviderConfiguration
     *
     * @param PaymentProviderConfiguration $paymentProviderConfiguration
     *
     * @return self
     */
    public function setPaymentProviderConfiguration($paymentProviderConfiguration)
    {
        $this->paymentProviderConfiguration = $paymentProviderConfiguration;
        return $this;
    }

    /**
     * Get paymentDate
     *
     * @return \DateTimLnull
     */
    public function getPaymentDate()
    {
        return $this->paymentDate;
    }

    /**
     * Set paymentDate
     *
     * @param \DateTimLnull $paymentDate
     *
     * @return self
     */
    public function setPaymentDate($paymentDate)
    {
        $this->paymentDate = $paymentDate;
        return $this;
    }

    /**
     * Return the pre tax order amount (en français prix hors taxes)
     * @param  int $defaultVatRate Default VAT rate
     * @return int|null
     */
    public function getTotalPretaxAmount($defaultVatRate = null)
    {
        if (null != $defaultVatRate && null == $this->totalPreTaxAmount) {
            $preTaxAmount = 0;
            foreach ($this->orderItems as $orderItem) {
                $preTaxAmount += $orderItem->getPreTaxPrice($defaultVatRate) * $orderItem->getQuantity();
            }

            $preTaxAmount +=  (int) round($this->shippingCost / (1 + $defaultVatRate/100));

            return $preTaxAmount;
        }
        return $this->totalPreTaxAmount;
    }

    /**
     * Set totalPreTaxAmount
     *
     * @param integer|null $totalPreTaxAmount
     *
     * @return self
     */
    public function setTotalPretaxAmount($totalPreTaxAmount)
    {
        $this->totalPreTaxAmount = $totalPreTaxAmount;
        return $this;
    }

    /**
     * Return the tax order amount
     * @param  int $defaultVatRate Default VAT rate
     * @return int
     */
    public function getTotalTaxAmount($defaultVatRate)
    {
        return $this->getTotalAmount() - $this->getTotalPreTaxAmount($defaultVatRate);
    }

    public function hasDiscounts()
    {
        foreach ($this->orderItems as $orderItem) {
            if ($orderItem->getPrice() < 0) {
                return true;
            }
        }
        return false;
    }

    public function getDiscounts()
    {
        $discounts = [];
        foreach ($this->orderItems as $orderItem) {
            if ($orderItem->getPrice() < 0) {
                $discounts[] = $orderItem;
            }
        }
        return $discounts;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set locale
     *
     * @param string $locale
     *
     * @return self
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
        return $this;
    }

    /**
     * Get delivery trackings
     *
     * @return ArrayCollection
     */
    public function getDeliveryTrackings()
    {
        return $this->deliveryTrackings;
    }

    /**
     * Set delivery trackings
     *
     * @param ArrayCollection $deliveryTrackings
     *
     * @return self
     */
    public function setDeliveryTrackings(ArrayCollection $deliveryTrackings)
    {
        foreach ($deliveryTrackings as $deliveryTracking) {
            $this->addDeliveryTracking($deliveryTracking);
        }
        return $this;
    }

    /**
     * Add delivery tracking
     *
     * @param DeliveryTracking $deliveryTracking
     *
     * @return self
     */
    public function addDeliveryTracking(DeliveryTracking $deliveryTracking)
    {
        if (!$this->deliveryTrackings->contains($deliveryTracking)) {
            $this->deliveryTrackings->add($deliveryTracking);
            if ($deliveryTracking->getOrder() != $this) {
                $deliveryTracking->setOrder($this);
            }
        }
        return $this;
    }

    /**
     * Remove delivery tracking
     *
     * @param DeliveryTracking $deliveryTracking
     *
     * @return self
     */
    public function removeDeliveryTracking($deliveryTracking)
    {
        if ($this->deliveryTrackings->contains($deliveryTracking)) {
            $this->deliveryTrackings->removeElement($deliveryTracking);
        }
        return $this;
    }
}
