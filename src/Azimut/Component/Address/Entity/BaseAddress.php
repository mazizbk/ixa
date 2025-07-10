<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-10-04 15:07:37
 */

namespace Azimut\Component\Address\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use JMS\Serializer\Annotation\Groups;

/**
 * Base address
 *
 * @ORM\MappedSuperclass
 */
class BaseAddress
{
    /**
     * @var string
     *
     * @ORM\Column(name="line1", type="string", length=255, nullable=true)
     * @Groups({"detail_address"})
     */
    protected $line1;

    /**
     * @var string
     *
     * @ORM\Column(name="line2", type="string", length=255, nullable=true)
     * @Groups({"detail_address"})
     */
    protected $line2;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=8, nullable=true)
     * @Groups({"detail_address"})
     */
    protected $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=255, nullable=true)
     * @Groups({"list_orders", "detail_address"})
     */
    protected $city;

    /**
     * @var string
     *
     * @ORM\Column(name="country", type="string", length=255)
     * @Groups({"list_orders", "detail_address"})
     */
    protected $country = 'FR';

    /**
     * Set line1
     *
     * @param string $line1
     *
     * @return self
     */
    public function setLine1($line1)
    {
        $this->line1 = $line1;

        return $this;
    }

    /**
     * Get line1
     *
     * @return string
     */
    public function getLine1()
    {
        return $this->line1;
    }

    /**
     * Set line2
     *
     * @param string $line2
     *
     * @return self
     */
    public function setLine2($line2)
    {
        $this->line2 = $line2;

        return $this;
    }

    /**
     * Get line2
     *
     * @return string
     */
    public function getLine2()
    {
        return $this->line2;
    }

    /**
     * Set postalCode
     *
     * @param string $postalCode
     *
     * @return self
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;
        $this->area = mb_substr($postalCode, 0, 2);

        return $this;
    }

    /**
     * Get postalCode
     *
     * @return string
     */
    public function getPostalCode()
    {
        return $this->postalCode;
    }

    /**
     * Set city
     *
     * @param string $city
     *
     * @return self
     */
    public function setCity($city)
    {
        $this->city = ucfirst($city);

        return $this;
    }

    /**
     * Get city
     *
     * @return string
     */
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set country
     *
     * @param string $country
     *
     * @return self
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * Get country
     *
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Print a formatted string
     *
     * @param string
     *
     * @return string
     */
    protected function toFormattedString($separator = "\n")
    {
        $string = '';

        if (!empty($this->line1)) {
            $string .= $this->line1.$separator;
        }

        if (!empty($this->line2)) {
            $string .= $this->line2.$separator;
        }

        if (!empty($this->postalCode)) {
            $string .= $this->postalCode;
            $string .= empty($this->city) ? $separator : ' ';
        }

        if (!empty($this->city)) {
            $string .= $this->city.$separator;
        }

        if (!empty($this->country)) {
            $string .= $this->country.$separator;
        }

        // Remove last separator
        if (!empty($string)) {
            $string = mb_substr($string, 0, - mb_strlen($separator));
        }

        return $string;
    }

    /**
     * Print as string
     *
     * @return string
     */
    public function __toString()
    {
        return $this->toFormattedString();
    }

    /**
     * Print as one line string
     *
     * @return string
     */
    public function toOneLineString()
    {
        return $this->toFormattedString(' ');
    }
}
