<?php

namespace Azimut\Bundle\SecurityBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="security_access_right_roles")
 * @DynamicInheritanceSubClass(discriminatorValue="roles")
 */
class AccessRightRoles extends AccessRight
{
    public function getAccessRightType()
    {
        return 'roles';
    }
}
