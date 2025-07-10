<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-11-08 10:31:04
 */

namespace Azimut\Bundle\SecurityBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;

/**
 * @ORM\Entity
 * @ORM\Table(name="security_access_right_acl")
 * @DynamicInheritanceSubClass(discriminatorValue="acl")
 */
class AccessRightAcl extends AccessRight
{
    /**
    * @ORM\OneToMany(targetEntity="Azimut\Bundle\SecurityBundle\Entity\Acl", mappedBy="accessRight", orphanRemoval=true, cascade={"persist"})
    */
    protected $acl;

    public function __construct()
    {
        $this->acl = new ArrayCollection();
    }

    public function getAcls()
    {
        return $this->acl;
    }

    public function setAcl(Acl $acl)
    {
        $this->acl = $acl;

        return $this;
    }

    //TODO  method find one by object from repository of Acl
    public function getObject()
    {
        $class = $this->acl->getObjectClass();
        $id = $this->acl->getObjectId();
        $object = findAclOneByObject($class, $id);

        return $object;
    }

    public function addAcl($acl)
    {
        if ($this->getAcls() == null) {
            $this->acl = new ArrayCollection();
        }
        if (!$this->getAcls()->contains($acl)) {
            $this->getAcls()->add($acl);
            $acl->setAccessRight($this);
        }

        return $this;
    }

    public function removeAcl($acl)
    {
        if ($this->getAcls() == null) {
            $this->acl = new ArrayCollection();
        }
        if ($this->getAcls()->contains($acl)) {
            $this->getAcls()->removeElement($acl);
            //do we delete the acl considering that it has no relation ???
        }

        return $this;
    }

    public function getAcl()
    {
        if ($this->acl != null) {
            return $this->acl;
        }
    }

    public function hasAcl($acl)
    {
        if ($acl != null) {
            return in_array($acl, $this->getAcls());
        }
    }

    public function getAccessRightType()
    {
        return 'acl';
    }
}
