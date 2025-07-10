<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-01-28 11:45:11
 */

namespace Azimut\Bundle\CmsContactBundle\Security;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\SecurityBundle\Security\BaseAccessRoleService;

class AccessRoleService extends BaseAccessRoleService
{
    public function __construct(RegistryInterface $registry, $activeBackofficeApps)
    {
        $namespace = '';
        $name = 'azimut_cmscontact_roles';//same as alias in service declaration used for ARType
        $roles =  array('APP_CMS_CONTACT');
        $entities = ['Azimut\Bundle\CmsContactBundle\Entity\CmsFileContact'];
        $rolesOnEntities = [
            'Azimut\Bundle\CmsContactBundle\Entity\CmsFileContact' => [
                'VIEW',
                'EDIT',
                //'SUGGEST',
                'VIEW_CONFIDENTIAL'
            ]
        ];

        parent::__construct($registry, $activeBackofficeApps, $name, $namespace, 'cms_contact', $roles, $rolesOnEntities, $entities);
    }
}
