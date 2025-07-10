<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-03-28 10:24:56
 */

namespace Azimut\Bundle\FrontofficeBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @DynamicInheritanceSubClass(discriminatorValue="date")
 */
class ZonePermanentDateFilter extends ZonePermanentFilter
{
    public function getComputedValue()
    {
        return date('Y-m-d H:i:s', strtotime($this->value));
    }
}
