<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-01-03 11:23:56
 */

namespace Azimut\Bundle\SecurityBundle\Security;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\SecurityBundle\Entity\AccessRole;

class AccessRoleService extends BaseAccessRoleService
{
    public function __construct(RegistryInterface $registry, $activeBackofficeApps, $locales = [])
    {
        $roles = array(
            'GLOBAL_ADMIN',
            'GLOBAL_EDIT_ALL_LANG',
            'APP_SECURITY',
            'SUPER_ADMIN'
        );
        /*foreach ($locales as $locale) {
            $roles[] = 'GLOBAL_EDIT_'.strtoupper($locale);
        }*/
        $name = 'azimut_security_roles';
        $namespace = 'Azimut\Bundle\SecurityBundle\Entity';
        parent::__construct($registry, $activeBackofficeApps, $name, $namespace, 'security', $roles);
    }

    /*  Returns a list of common roles between this role provider and database.
        Role will be created if it doesn't exist in database,
        used in form.type.accessrightrole
    */
    public function getCommonRoles()
    {
        $doctrine = $this->registry;
        $repository = $doctrine->getRepository('AzimutSecurityBundle:AccessRole');
        $commonRoles = [];

        foreach ($this->getRoles() as $role) {
            $roleInDB = $repository->findOneBy(array('role'=>$role));
            if (null === $roleInDB) {
                $roleInDB = new AccessRole();
                $roleInDB->setRole($role);
                $doctrine->getManager()->persist($roleInDB);
            }
            if ($roleInDB->getRole() != "SUPER_ADMIN" && $roleInDB->getRole() != "APP_SECURITY") {
                $commonRoles[] = $roleInDB;
            }
        }
        return $commonRoles;
    }
}
