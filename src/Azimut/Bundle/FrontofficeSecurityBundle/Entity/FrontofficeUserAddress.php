<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-10-04 15:13:29
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Component\Address\Entity\BaseAddress;

/**
 * Address
 *
 * @ORM\Table(name="frontoffice_security_user_address")
 * @ORM\Entity()
 */
class FrontofficeUserAddress extends BaseAddress
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Create a new instance from a BaseAddress object
     *
     * @param  BaseAddress $baseAddress
     * @return FrontofficeUserAddress
     */
    static function createFromBaseAddress(BaseAddress $baseAddress)
    {
        $userAddress = new FrontofficeUserAddress();
        $userAddress
            ->setLine1($baseAddress->getLine1())
            ->setLine2($baseAddress->getLine2())
            ->setPostalCode($baseAddress->getPostalCode())
            ->setCity($baseAddress->getCity())
            ->setCountry($baseAddress->getCountry())
        ;
        return $userAddress;
    }
}
