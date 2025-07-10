<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-04-28 11:23:56
 */

namespace Azimut\Bundle\SecurityBundle\Services;

class Canonicalizer
{
    public function canonicalize($string)
    {
        return null === $string ? null : mb_convert_case($string, MB_CASE_LOWER, mb_detect_encoding($string));
    }
}

/*
* Canonical fields get lowercased before comparison or search — to make sure there
* are no duplicates with the same value but with different case like Test@test.org
* and test@test.org.
*/
