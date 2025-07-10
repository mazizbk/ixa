<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-21 15:31:04
 */

namespace Azimut\Bundle\SecurityBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass="Azimut\Bundle\SecurityBundle\Entity\Repository\AccessRightClassRepository")
 * @ORM\Table(name="security_access_right_class")
 * @DynamicInheritanceSubClass(discriminatorValue="class")
 *
 */
class AccessRightClass extends AccessRight
{
    /**
     * @var string
     *
     * @ORM\Column(type="string")
     * @Groups({"detail_access_right", "list_access_rights"})
     */
    protected $class;

    public function getClass()
    {
        return $this->class;
    }

    public function setClass($class)
    {

       /* if (false !== $pos = strrpos($class, '\\')) {
            $this->class = substr($class, $pos + 1);
        }*/
        $this->class = $class;

        return $this;
    }

    public function getAccessRightType()
    {
        return 'class';
    }
}
