<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-15 16:25:17
 */

namespace Azimut\Bundle\SecurityBundle\Entity;

class AclField
{
    private $object;
    private $field;

    public function __construct($obj, $field)
    {
        $this->object = $obj;
        $this->field = $field;
    }

    public function getObject()
    {
        return $this->object; //class
    }

    public function getField()
    {
        return $this->field;
    }
}
