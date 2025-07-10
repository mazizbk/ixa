<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-04 09:25:02
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\Security;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\SecurityBundle\Security\BaseAccessRoleService;

class AccessRoleService extends BaseAccessRoleService
{
    public function __construct(RegistryInterface $registry, $activeBackofficeApps, $allowFrontUserImpersonation)
    {
        $namespace = '';
        $name = 'azimut_frontofficesecurity_roles';

        $roles =  [
            'APP_FRONTOFFICE_SECURITY',
        ];

        if ($allowFrontUserImpersonation) {
            $roles[] = 'GLOBAL_IMPERSONATE_USER';
        }

        parent::__construct($registry, $activeBackofficeApps, $name, $namespace, 'frontoffice_security', $roles);
    }
}
