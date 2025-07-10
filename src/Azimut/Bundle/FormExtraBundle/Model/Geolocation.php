<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-08-19 16:36:40
 */

namespace Azimut\Bundle\FormExtraBundle\Model;

use JMS\Serializer\Annotation\Groups;

class Geolocation implements \Serializable
{
    /**
     * @var float
     *
     * @Groups({"always"})
     */
    protected $latitude;

    /**
     * @var float
     *
     * @Groups({"always"})
     */
    protected $longitude;

    public function __construct($latitude = null, $longitude = null)
    {
        $this->latitude = $latitude;
        $this->longitude = $longitude;
    }

    public function getLatitude()
    {
        return $this->latitude;
    }

    public function setLatitude($latitude)
    {
        $this->latitude = $latitude;
    }

    public function getLongitude()
    {
        return $this->longitude;
    }

    public function setLongitude($longitude)
    {
        $this->longitude = $longitude;
    }

    public function serialize()
    {
        return serialize([
            'latitude' => $this->latitude,
            'longitude' => $this->longitude
        ]);
    }

    public function unserialize($serialized)
    {
        $object = unserialize($serialized);

        $this->latitude = $object['latitude'];
        $this->longitude = $object['longitude'];
    }
}
