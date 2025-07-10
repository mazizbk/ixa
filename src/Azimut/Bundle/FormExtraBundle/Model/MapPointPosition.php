<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-24 10:38:23
 */

namespace Azimut\Bundle\FormExtraBundle\Model;

use JMS\Serializer\Annotation\Groups;

class MapPointPosition implements \Serializable
{
    /**
     * @var float
     *
     * @Groups({"always"})
     */
    protected $x;

    /**
     * @var float
     *
     * @Groups({"always"})
     */
    protected $y;

    public function __construct($x = null, $y = null)
    {
        $this->x = $x;
        $this->y = $y;
    }

    public function getX()
    {
        return $this->x;
    }

    public function setX($x)
    {
        $this->x = $x;
    }

    public function getY()
    {
        return $this->y;
    }

    public function setY($y)
    {
        $this->y = $y;
    }

    public function serialize()
    {
        return serialize([
            'x' => $this->x,
            'y' => $this->y
        ]);
    }

    public function unserialize($serialized)
    {
        $object = unserialize($serialized);

        $this->x = $object['x'];
        $this->y = $object['y'];
    }
}
