<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-11 09:58:51
 */

namespace Azimut\Bundle\ShopBundle\Service\Payment;

class PaymentProviderChain
{
    /**
     * @var PaymentProviderInterface[]
     */
    private $providers;

    public function __construct()
    {
        $this->providers = [];
    }

    /**
     * Has providers
     *
     * @return boolean
     */
    public function hasProviders()
    {
        return count($this->providers) > 0;
    }

    /**
     * Get provider
     *
     * @return PaymentProviderInterface
     */
    public function getProvider($id)
    {
        if (!isset($this->providers[$id])) {
            throw new \Exception(sprintf('Unknown payment provider with id "%s"', $id));
        }
        return $this->providers[$id];
    }

    /**
     * Get providers
     *
     * @return [PaymentProviderInterface]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Add provider
     *
     * @param PaymentProviderInterface $provider Provider
     * @param string                   $id       Provider id in the service container
     */
    public function addProvider(PaymentProviderInterface $provider, $id)
    {
        $provider->setId($id);
        $this->providers[$id] = $provider;

        return $this;
    }
}
