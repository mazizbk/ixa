<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-11-08 11:27:25
 */

namespace Azimut\Bundle\ShopBundle\Service\Delivery;

use Symfony\Component\Translation\TranslatorInterface;
use Doctrine\ORM\EntityManagerInterface;

use Azimut\Bundle\ShopBundle\Entity\Order;

class StandardDeliveryProvider extends AbstractDeliveryProvider
{
    /**
     * @var int
     */
    private $shippingCost;

    /**
     * @var int
     */
    private $freeShippingAmount;

    /**
     * @param int $shippingCost Standard shipping price
     * @param int $freeShippingAmount Min order amount for free shipping
     */
    public function __construct(TranslatorInterface $translator, EntityManagerInterface $entityManager, $shippingCost, $freeShippingAmount)
    {
        parent::__construct($translator, $entityManager);
        $this->shippingCost = $shippingCost;
        $this->freeShippingAmount = $freeShippingAmount;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->translator->trans('standard.delivery');
    }

    /**
     * {@inheritdoc}
     */
    public function getImage()
    {
        return 'bundles/azimutshop/img/standard-delivery.png';
    }

    /**
     * {@inheritdoc}
     */
    public function getDescription()
    {
        return $this->translator->trans('Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua');
    }

    /**
     * {@inheritdoc}
     */
    public function isAvailableForOrder(Order $order)
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getShippingCost(Order $order)
    {
        if ($order->getTotalAmount() >= $this->freeShippingAmount) {
            return 0;
        }

        return $this->shippingCost;
    }
}
