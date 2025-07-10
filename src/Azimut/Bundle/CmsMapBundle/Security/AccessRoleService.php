<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:    2016-08-22 12:15:11
 */

namespace Azimut\Bundle\CmsMapBundle\Security;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\SecurityBundle\Security\BaseAccessRoleService;

class AccessRoleService extends BaseAccessRoleService
{
    public function __construct(RegistryInterface $registry, $activeBackofficeApps)
    {
        $namespace = '';
        $name = 'azimut_cmsmap_roles';//same as alias in service declaration used for ARType
        $roles =  array('APP_CMS_MAP');
        $entities = ['Azimut\Bundle\CmsMapBundle\Entity\CmsFileMapPoint'];
        $rolesOnEntities = [
            'Azimut\Bundle\CmsMapBundle\Entity\CmsFileMapPoint' => [
                'VIEW',
                'EDIT',
            ]
        ];

        parent::__construct($registry, $activeBackofficeApps, $name, $namespace, 'cms_map', $roles, $rolesOnEntities, $entities);
    }
}
