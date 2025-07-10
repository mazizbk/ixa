<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-01-28 11:45:11
 */

namespace Azimut\Bundle\DemoAngularJsBundle\Security;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\SecurityBundle\Security\BaseAccessRoleService;

class AccessRoleService extends BaseAccessRoleService
{
    public function __construct(RegistryInterface $registry, $activeBackofficeApps)
    {
        $namespace = '';
        $name = 'azimut_demoangularjs_roles';//same as alias in service declaration used for ARType
        $roles =  [
            'APP_DEMO_ANGULAR_JS',
            'GLOBAL_DEMO_SIMPLE_ROLE',
        ];
        parent::__construct($registry, $activeBackofficeApps, $name, $namespace, 'demo_angular_js', $roles);
    }
}
