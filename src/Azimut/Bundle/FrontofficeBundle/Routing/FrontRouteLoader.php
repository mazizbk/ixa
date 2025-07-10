<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-03-15 10:22:23
 */

namespace Azimut\Bundle\FrontofficeBundle\Routing;

use Symfony\Component\Config\Loader\Loader;
use Symfony\Component\Routing\Route;

class FrontRouteLoader extends Loader
{
    /**
     * @var array
     */
    private $frontLocales;

    /**
     * @var bool
     */
    private $useFrontUrlLocalePrefixIfOneLocale;

    public function __construct(array $frontLocales, $useFrontUrlLocalePrefixIfOneLocale)
    {
        $this->frontLocales = $frontLocales;
        $this->useFrontUrlLocalePrefixIfOneLocale = $useFrontUrlLocalePrefixIfOneLocale;
    }

    public function load($resource, $type = null)
    {
        // Disabled translated routes if param enabled and only one locale
        if (false === $this->useFrontUrlLocalePrefixIfOneLocale && 2 > count($this->frontLocales)) {
            $options = [
                'i18n' => false,
            ];
        }
        else {
            $options = [
                'i18n_locales' => $this->frontLocales,
            ];
        }

        $delegateRouteLoader = $this->resolve($resource);
        /** @var Route[] $routes */
        $routes = $delegateRouteLoader->load($resource);

        foreach ($routes as $route) {
            $route->setOptions(array_merge($route->getOptions(), $options));
        }

        return $routes;
    }

    public function supports($resource, $type = null)
    {
        return 'azimut_frontoffice' === $type;
    }
}
