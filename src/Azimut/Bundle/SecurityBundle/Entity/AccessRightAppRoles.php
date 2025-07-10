<?php
/**
 * Created by PhpStorm.
 * User: gerdald
 * Date: 08/09/14
 * Time: 14:10
 */

namespace Azimut\Bundle\SecurityBundle\Entity;

use Azimut\Bundle\DoctrineExtraBundle\Configuration\DynamicInheritanceSubClass;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="security_access_right_app_roles")
 * @DynamicInheritanceSubClass(discriminatorValue="app_roles")
 */
class AccessRightAppRoles extends AccessRight
{
    public function getAccessRightType()
    {
        return 'app_roles';
    }
}
