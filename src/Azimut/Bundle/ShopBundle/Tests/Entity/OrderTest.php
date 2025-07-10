<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-03-12 10:07:43
 */

namespace Azimut\Bundle\ShopBundle\Tests\Entity;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\Translator;
use Doctrine\ORM\EntityManager;
use Azimut\Bundle\ShopBundle\Entity\Order;
use Azimut\Bundle\ShopBundle\Entity\OrderItem;
use Azimut\Bundle\ShopBundle\Service\Delivery\StandardDeliveryProvider;

/**
 * This is a partial test, it has to completed
 */
class OrderTest extends TestCase {

    /**
     * @var Order
     */
    private $order;

    /**
     * @var StandardDeliveryProvider
     */
    private $standardDeliveryProvider;

    /**
     * @var int
     */
    private $defaultVatRate = 20;

    public function setUp()
    {
        $shippingCost = 650;
        $freeShippingAmount = 1000000000;

        parent::setUp();
        $this->order = new Order();
        $translatorMock = $this->prophesize(Translator::class);
        $entityManagerMock = $this->prophesize(EntityManager::class);
        $this->standardDeliveryProvider = new StandardDeliveryProvider($translatorMock->reveal(), $entityManagerMock->reveal(), $shippingCost, $freeShippingAmount);
    }

    public function testGetterAndSetter()
    {
        $item1 = new OrderItem();
        $item1
            ->setName('Item 1')
            ->setQuantity(1)
            ->setPrice(20000)
            ->setOrder($this->order)
        ;
        $item2 = new OrderItem();
        $item2
            ->setName('Item 2')
            ->setQuantity(4)
            ->setPrice(10000)
            ->setOrder($this->order)
        ;

        // Check amounts calculation

        $this->assertEquals($this->order->getTotalItemsAmount(), 60000);
        $this->assertEquals($this->order->getTotalAmount(), 60000);

        // Check amounts calculation with delivery cost

        $this->order->setDeliveryProvider($this->standardDeliveryProvider);

        $this->assertEquals($this->order->getTotalItemsAmount(), 60000);
        $this->assertEquals($this->order->getTotalAmount(), 60650);

        // Check VAT handling
        // NB: rounds are applied individually on each item price

        $this->assertEquals(50541, $this->order->getTotalPreTaxAmount($this->defaultVatRate));

        // VAT 5.5% (55‰)
        $item2->setVatRate(55);
        $this->assertEquals(55125, $this->order->getTotalPreTaxAmount($this->defaultVatRate));


        // Check total amount caches

        $this->order->setTotalAmount(100000);
        $this->assertEquals($this->order->getTotalAmount(), 100000);

        $this->order->setTotalPreTaxAmount(80000);
        $this->assertEquals($this->order->getTotalPreTaxAmount(), 80000);
    }

    protected function tearDown()
    {
        unset($this->order);
    }
}
