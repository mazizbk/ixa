<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2015-11-04 10:31:04
 */

namespace Azimut\Bundle\SecurityBundle\Security;

use Azimut\Bundle\SecurityBundle\Entity\AccessRight;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\VirtualProperty;
use JMS\Serializer\Annotation\Groups;

trait ObjectAccessRightAware
{
    public function addAccessRight(AccessRight $ar)
    {
        if (! $this->getAccessRights()->contains($ar)) {
            $this->getAccessRights()->add($ar);
        }

        return $this;
    }

    /**
     * @return AccessRight[]|ArrayCollection<AccessRight>
     */
    public function getAccessRights()
    {
        return $this->accessRights;
    }


    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    public function getClass()
    {
        return get_class($this);
    }


    public function createAccessRight()
    {
        $className = $this->getAccessRightClassName();
        return new $className;
    }


    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    abstract public function __toString();

    /*
     * Used for Voter to determine the access rights class.
     */
    abstract public function getAccessRightClassName();

    /**
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    abstract public function getAccessRightType();

    abstract public function getParentsClassesSecurityContextObject();

    abstract public function getChildrenClassesSecurityContextObject();


    /*
     * Voters needs to know parents
     * to be able to identify its rights
     */
    abstract public function getParentsSecurityContextObject();

    /*
     * Access Right object needs to know sub-objects
     * to be able to identify its children
     *
     * @VirtualProperty
     * @Groups({"security_access_right_obj"})
     */
    abstract public function getChildrenSecurityContextObject();
}
