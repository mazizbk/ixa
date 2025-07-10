<?php
/**
 * Created by mikaelp on 25-Jul-18 10:31 AM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Intl\Intl;

/**
 * @ORM\Embeddable()
 */
class PostalAddress
{
    /**
     * @var string
     *
     * @ORM\Column(name="line1", type="string", length=255, nullable=true)
     */
    private $line1;

    /**
     * @var string
     *
     * @ORM\Column(name="line2", type="string", length=255, nullable=true)
     */
    private $line2;

    /**
     * @var string
     *
     * @ORM\Column(name="postalCode", type="string", length=5, nullable=true)
     */
    private $postalCode;

    /**
     * @var string
     *
     * @ORM\Column(name="city", type="string", length=60, nullable=true)
     */
    private $city;

    /**
     * @var string
     *
     * @ORM\Column(type="string", length=5, nullable=true)
     */
    private $country = 'FR';


    /**
     * Set line1
     *
     * @param string $line1
     *
     * @return PostalAddress
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
     * @return PostalAddress
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
     * @return PostalAddress
     */
    public function setPostalCode($postalCode)
    {
        $this->postalCode = $postalCode;

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
     * @return PostalAddress
     */
    public function setCity($city)
    {
        $this->city = $city;

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
     * @return string
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param string $country
     * @return $this
     */
    public function setCountry($country)
    {
        $this->country = $country;

        return $this;
    }

    /**
     * @return string
     */
    public function getCountryName()
    {
        return Intl::getRegionBundle()->getCountryName($this->country);
    }

    public function isFilled()
    {
        // Country is FR by default so it's excluded from here
        return strlen($this->line1.$this->line2.$this->postalCode.$this->city)>0;
    }

}
