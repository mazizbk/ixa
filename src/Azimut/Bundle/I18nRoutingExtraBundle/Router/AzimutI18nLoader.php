<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-04-26 11:30:09
 */

namespace Azimut\Bundle\I18nRoutingExtraBundle\Router;

use JMS\I18nRoutingBundle\Router\I18nLoader;
use Symfony\Component\Routing\RouteCollection;

class AzimutI18nLoader extends I18nLoader
{
    public function load(RouteCollection $collection)
    {
        $i18nCollection = parent::load($collection);

        foreach ($i18nCollection->all() as $name => $route) {
            if (null != $locale = $route->getDefault('_locale')) {
                // Remove home page locale prefix trailing slash
                if ('/'.$locale.'/' == $route->getPath()) {
                    $route->setPath('/'.$locale);
                }
            }
        }

        return $i18nCollection;
    }
}
