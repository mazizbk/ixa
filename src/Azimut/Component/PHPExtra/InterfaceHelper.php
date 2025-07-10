<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-05-24 11:39:16
 */

namespace Azimut\Component\PHPExtra;

class InterfaceHelper
{
    /**
     * Check if a class implements a specific interface
     * If the class is instanciated, you can use instanceof instead
     */
    public static function isClassImplementing($class, $interface)
    {
        $implementedInterfaces = class_implements($class);
        return in_array($interface, $implementedInterfaces);
    }
}
