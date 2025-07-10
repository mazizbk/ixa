<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-03 10:07:18
 */

namespace Azimut\Bundle\FrontofficeBundle\Service;

class SearchEngineProviderChain
{
    /**
     * @var SearchEngineProviderInterface[]
     */
    private $providers;

    public function __construct()
    {
        $this->providers = [];
    }

    public function hasProviders()
    {
        return count($this->providers) > 0;
    }

    public function getProviders()
    {
        return $this->providers;
    }

    public function addProvider(SearchEngineProviderInterface $provider)
    {
        $this->providers[] = $provider;

        return $this;
    }
}
