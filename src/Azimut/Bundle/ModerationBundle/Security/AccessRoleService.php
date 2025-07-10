<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-06-27 11:58:28
 */

namespace Azimut\Bundle\ModerationBundle\Security;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\SecurityBundle\Security\BaseAccessRoleService;

class AccessRoleService extends BaseAccessRoleService
{
    public function __construct(RegistryInterface $registry, $activeBackofficeApps)
    {
        $namespace = '';
        $name = 'azimut_moderation_roles'; // same as alias in service declaration used for ARType
        $roles =  [
            'APP_MODERATION',
        ];
        parent::__construct($registry, $activeBackofficeApps, $name, $namespace, 'moderation', $roles);
    }
}
