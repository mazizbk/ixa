<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-11-04 17:24:57
 */

namespace Azimut\Component\PHPExtra;

class TraitHelper
{
    /**
     * Performs a classic class_uses() on the class AND its parent classes and traits
     */
    public static function deepClassUses($class, $autoload = true)
    {
        $traits = [];

        // find traits used by class and parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        // find traits used by traits and parent traits
        $traitsToCheck = $traits;
        while (!empty($traitsToCheck)) {
            $foundTraits = class_uses(array_pop($traitsToCheck), $autoload);
            $traits = array_merge($foundTraits, $traits);
            $traitsToCheck = array_merge($foundTraits, $traitsToCheck);
        };

        return array_unique($traits);
    }

    /**
     * Check if a class uses a specific trait
     */
    public static function isClassUsing($class, $trait, $recursive = true)
    {
        if ($recursive) {
            $usedTraits = self::deepClassUses($class);
        } else {
            $usedTraits = class_uses($class);
        }

        return in_array($trait, $usedTraits);
    }
}
