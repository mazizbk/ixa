<?php
/**
 * Created by mikaelp on 2018-11-14 10:03 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\DBAL\Types;


use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\SimpleArrayType;

/**
 * Does the same thing as SimpleArrayType, but keeps the null PHP value
 */
class NullableSimpleArrayType extends SimpleArrayType
{
    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        if ($value === null) {
            return null;
        }

        return parent::convertToPHPValue($value, $platform);
    }

    public function getName()
    {
        return 'nullable_simple_array';
    }

}
