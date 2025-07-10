<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-11-08 11:53:34
 */

namespace Azimut\Bundle\ShopBundle\Service\Delivery;

class DeliveryProviderChain
{
    /**
     * @var DeliveryProviderInterface[]
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
     * Get providers
     *
     * @return [DeliveryProviderInterface]
     */
    public function getProviders()
    {
        return $this->providers;
    }

    /**
     * Add provider
     *
     * @param DeliveryProviderInterface $provider Provider
     * @param string                    $id       Provider id in the service container
     */
    public function addProvider(DeliveryProviderInterface $provider, $id)
    {
        $provider->setId($id);
        $this->providers[$id] = $provider;

        return $this;
    }

    /**
     * Get provider
     *
     * @param string $id Provider id in the service container
     *
     * @return DeliveryProviderInterface
     */
    public function getProvider($id)
    {
        if (!isset($this->providers[$id])) {
            throw new \Exception(sprintf('Unknown payment provider with id "%s"', $id));
        }
        return $this->providers[$id];
    }
}
