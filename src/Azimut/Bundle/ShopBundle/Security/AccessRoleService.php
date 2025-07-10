<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-09-14 14:43:41
 */

namespace Azimut\Bundle\ShopBundle\Security;

use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\SecurityBundle\Security\BaseAccessRoleService;

class AccessRoleService extends BaseAccessRoleService
{
    public function __construct(RegistryInterface $registry, $activeBackofficeApps)
    {
        $namespace = '';
        $name = 'azimut_shop_roles';//same as alias in service declaration used for ARType
        $roles =  [
            'APP_SHOP',
            'GLOBAL_DEMO_SIMPLE_ROLE',
        ];
        parent::__construct($registry, $activeBackofficeApps, $name, $namespace, 'shop', $roles);
    }
}
