<?php
/*
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-21 10:39:39
 */

namespace Azimut\Bundle\DemoSecurityInjectionBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\Groups;

/**
 * @ORM\Entity
 * @ORM\Table(name="demo_security_access_right_entity")
 *
 * @DynamicInheritanceSubClass(discriminatorValue="access_right_demo_entity")
 */
class AccessRightDemoEntity extends AccessRight
{
    /**
    * @ORM\ManyToOne(targetEntity="Azimut\Bundle\DemoSecurityInjectionBundle\Entity\DemoEntity", inversedBy="accessRights")
    * @ORM\JoinColumn(name="entity_id", referencedColumnName="id")
    */
    protected $entity;

    public function getEntity()
    {
        return $this->entity;
    }

    public function getObject()
    {
        return $this->entity;
    }

    public function setObject($entity)
    {
        return $this->entity = $entity;
    }

    /**
     * @VirtualProperty
     * @Groups({"list_access_rights"})
     */
    public function getObjectId()
    {
        if (null === $this->getObject()) {
            return null;
        }

        return $this->getObject()->getId();
    }

    /**
     * @VirtualProperty
     * @Groups({"detail_access_right", "list_access_rights"})
     */
    public function getAccessRightType()
    {
        return 'demo';
    }
}
