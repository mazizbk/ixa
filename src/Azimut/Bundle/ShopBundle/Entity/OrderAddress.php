<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-14 15:25:45
 */

namespace Azimut\Bundle\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

use Azimut\Component\Address\Entity\BaseAddress;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUserAddress;

/**
 * OrderAddress
 *
 * @ORM\Table(name="shop_order_address")
 * @ORM\Entity()
 */
class OrderAddress extends BaseAddress
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
     * @var string
     *
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     * @Groups({"list_orders", "detail_address"})
     */
    protected $firstName;

    /**
     * @var string
     *
     * @ORM\Column(name="last_name", type="string", length=255)
     * @Groups({"list_orders", "detail_address"})
     */
    protected $lastName;

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
     * Get firstName
     *
     * @return string
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * Set firstName
     *
     * @param string $firstName
     *
     * @return self
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
        return $this;
    }

    /**
     * Get lastName
     *
     * @return string
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * Set lastName
     *
     * @param string $lastName
     *
     * @return self
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * Create a new instance from a FrontofficeUserAddress object
     *
     * @param  FrontofficeUserAddress $userAddress
     * @return OrderAddress
     */
    static function createFromUserAddress(FrontofficeUser $user, FrontofficeUserAddress $userAddress)
    {
        $orderAddress = new OrderAddress();
        $orderAddress
            ->setFirstName($user->getFirstName())
            ->setLastName($user->getLastName())
            ->setLine1($userAddress->getLine1())
            ->setLine2($userAddress->getLine2())
            ->setPostalCode($userAddress->getPostalCode())
            ->setCity($userAddress->getCity())
            ->setCountry($userAddress->getCountry())
        ;
        return $orderAddress;
    }
}
