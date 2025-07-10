<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-14 15:36:01
 */

namespace Azimut\Bundle\ShopBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\ShopBundle\Entity\OrderItem;
use Azimut\Bundle\ShopBundle\Entity\OrderAddress;
use Azimut\Bundle\ShopBundle\Entity\DeliveryTracking;
use Azimut\Bundle\ShopBundle\Service\OrderStatusProvider;

class LoadOrderData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
    * {@inheritDoc}
    */
    public function load(ObjectManager $manager)
    {
        $order = new Order();
        $billingAddress = new OrderAddress;
        $billingAddress
            ->setFirstName('Pedro')
            ->setLastName('Bottcher')
            ->setLine1('47 Lorem Street')
            ->setPostalCode('88888')
            ->setCity('Epsum')
        ;
        $deliveryAddress = new OrderAddress;
        $deliveryAddress
            ->setFirstName('Linette')
            ->setLastName('Stracke')
            ->setLine1('34 Main Street')
            ->setPostalCode('99999')
            ->setCity('Quande')
        ;

        $order
            ->setNumber('20190312001')
            ->setOrderDate(\DateTime::createFromFormat('Y-m-d', '2019-03-12'))
            ->setSite($this->getReference('site1'))
            ->setLocale('en')
            ->setShippingCost(650)
            ->setTotalAmount(110650)
            ->setStatus(OrderStatusProvider::STATUS_PAID)
            ->setBillingAddress($billingAddress)
            ->setDeliveryAddress($deliveryAddress)
            ->setDeliveryProviderId('azimut_shop.standard_delivery_provider')
            ->setClientComment("This is a client comment\nIts is multiline")
            ->setPrivateComment("This is a private comment\nOnly visible by shop owner")
        ;
        $manager->persist($order);

        $orderItem = new OrderItem();
        $orderItem
            ->setName('My item', 'en')
            ->setName('Mon item', 'fr')
            ->setPrice(10000)
            ->setOrder($order)
        ;
        $manager->persist($orderItem);

        $orderItem = new OrderItem();
        $orderItem
            ->setName('My other item', 'en')
            ->setName('Mon autre item', 'fr')
            ->setQuantity(4)
            ->setPrice(25000)
            ->setOrder($order)
        ;
        $manager->persist($orderItem);

        $deliveryTracking = new DeliveryTracking();
        $deliveryTracking
            ->setOrder($order)
            ->setCode('API3HTOUVMMR')
            ->setLabel('Colis livré')
            ->setShippingDate(\DateTime::createFromFormat('Y-m-d', '2019-03-12'))
            ->setDeliveryDate(\DateTime::createFromFormat('Y-m-d', '2019-03-13'))
            ->isDelivered(true)
        ;
        $manager->persist($deliveryTracking);

        $deliveryTracking = new DeliveryTracking();
        $deliveryTracking
            ->setOrder($order)
            ->setCode('U9QHGVDBDI8BRQUZYPTU')
            ->setLabel('Colis pris en charge')
            ->setShippingDate(\DateTime::createFromFormat('Y-m-d', '2019-03-12'))
        ;
        $manager->persist($deliveryTracking);

        // --

        $order = new Order();
        $order
            ->setNumber('20180914002')
            ->setOrderDate(new \DateTime('2018-09-16'))
            ->setSite($this->getReference('site1'))
            ->setLocale('en')
            ->setStatus(OrderStatusProvider::STATUS_VALIDATED)
            ->setTotalAmount(40000)
        ;
        $manager->persist($order);

        $orderItem = new OrderItem();
        $orderItem
            ->setName('My demo item', 'en')
            ->setName('Mon item de démo', 'fr')
            ->setPrice(25000)
            ->setOrder($order)
        ;
        $manager->persist($orderItem);

        // --
        // A basket

        $order = new Order();
        $order
            ->setSite($this->getReference('site1'))
            ->setLocale('en')
        ;
        $manager->persist($order);

        $orderItem = new OrderItem();
        $orderItem
            ->setName('My basket item', 'en')
            ->setName('Mon item de panier', 'fr')
            ->setPrice(50000)
            ->setOrder($order)
        ;
        $manager->persist($orderItem);

        // --
        // Old baskets

        $order = new Order();
        $order
            ->setSite($this->getReference('site1'))
            ->setLocale('en')
            ->setCreatedAt(\DateTime::createFromFormat('Y-m-d', '2019-01-15'))
            ->setUpdatedAt(\DateTime::createFromFormat('Y-m-d', '2019-01-15'))
        ;
        $manager->persist($order);
        $order = new Order();
        $order
            ->setSite($this->getReference('site1'))
            ->setLocale('en')
            ->setCreatedAt(\DateTime::createFromFormat('Y-m-d', '2019-01-15'))
            ->setUpdatedAt(\DateTime::createFromFormat('Y-m-d', '2019-01-15'))
        ;
        $manager->persist($order);

        // --
        // Old order

        $order = new Order();
        $order
            ->setSite($this->getReference('site1'))
            ->setLocale('en')
            ->setNumber('20190115001')
            ->setCreatedAt(\DateTime::createFromFormat('Y-m-d', '2019-01-15'))
            ->setUpdatedAt(\DateTime::createFromFormat('Y-m-d', '2019-01-15'))
            ->setOrderDate(\DateTime::createFromFormat('Y-m-d', '2019-01-15'))
            ->setShippingCost(650)
            ->setTotalAmount(110650)
            ->setStatus(OrderStatusProvider::STATUS_PAID)
        ;

        $manager->flush();
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 20;
    }
}
