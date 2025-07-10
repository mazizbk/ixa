<?php

/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-13 14:00:32
 */

namespace Azimut\Bundle\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @var int
 *
 * @ORM\Entity(repositoryClass="")
 * @DynamicInheritanceSubClass(discriminatorValue="demo")
 */
class PaymentProviderConfigurationDemo extends PaymentProviderConfiguration
{
    /**
     * @var string
     *
     * @ORM\Column(type="string", length=10)
     */
    protected $currency;

    /**
     * @param string $currency
     */
    public function __construct($currency)
    {
        $this->currency = $currency;
    }

    /**
     * Get currency
     *
     * @return string
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * Set currency
     *
     * @param string $currency
     *
     * @return self
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }


    /*
     Example if we wanted to plug JMSPaymentCoreBundle :

    / **
     * @ORM\OneToOne(targetEntity="JMS\Payment\CoreBundle\Entity\PaymentInstruction")
     * /
    private $paymentInstruction;

    / **
     * Get paymentInstruction
     *
     * @return PaymentInstruction
     * /
    public function getPaymentInstruction()
    {
        return $this->paymentInstruction;
    }

    / **
     * Set paymentInstruction
     *
     * @param PaymentInstruction $paymentInstruction
     *
     * @return self
     * /
    public function setPaymentInstruction($paymentInstruction)
    {
        $this->paymentInstruction = $paymentInstruction;
        return $this;
    }
    */
}
