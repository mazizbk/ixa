<?php
/*
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-21 11:23:56
 */

namespace Azimut\Bundle\DemoSecurityInjectionBundle\Security;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\SecurityBundle\Security\BaseAccessRoleService;

class AccessRoleService extends BaseAccessRoleService
{
    public function __construct(RegistryInterface $registry, $activeBackofficeApps)
    {
        $name = 'azimut_demo_security_injection_roles'; //same as alias in service declaration used for ARType
        $namespace = 'Azimut\Bundle\DemoSecurityInjectionBundle\Entity';
        $entities = ['Azimut\Bundle\DemoSecurityInjectionBundle\Entity\DemoEntity'];
        $roles =  array('APP_DEMO_SECURITY_INJECTION');
        $rolesOnEntities['Azimut\Bundle\DemoSecurityInjectionBundle\Entity\DemoEntity'] = [
                'CREATE',
                'VIEW',
                'EDIT',
                'DELETE'
        ];

        parent::__construct($registry, $activeBackofficeApps, $name, $namespace, 'demo_security_injection', $roles, $rolesOnEntities, $entities);
    }
}
