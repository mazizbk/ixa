<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-09 14:45:13
 */

namespace Azimut\Bundle\DemoExternalAppBundle\Security;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\SecurityBundle\Security\BaseAccessRoleService;

class AccessRoleService extends BaseAccessRoleService
{
    public function __construct(RegistryInterface $registry, $activeBackofficeApps)
    {
        $namespace = '';
        $name = 'azimut_demoexternalapp_roles';//same as alias in service declaration used for ARType
        $roles =  array('APP_DEMO_EXTERNAL_APP');
        parent::__construct($registry, $activeBackofficeApps, $name, $namespace, 'demo_external_app', $roles);
    }
}
