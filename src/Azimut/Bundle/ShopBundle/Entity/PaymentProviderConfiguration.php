<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-13 13:56:49
 */

namespace Azimut\Bundle\ShopBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceMap;

/**
 * @var int
 *
 * @ORM\Entity()
 * @ORM\Table(name="shop_payment_provider_configuration")
 * @ORM\InheritanceType("SINGLE_TABLE")
 * @ORM\DiscriminatorColumn(name="type", type="string")
 * @DynamicInheritanceMap
 */
class PaymentProviderConfiguration
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}
