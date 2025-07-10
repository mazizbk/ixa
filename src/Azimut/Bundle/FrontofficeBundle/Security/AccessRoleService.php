<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:    2014-01-06 11:23:56
 */

namespace Azimut\Bundle\FrontofficeBundle\Security;

use Azimut\Bundle\FrontofficeBundle\Entity\Page;
use Azimut\Bundle\FrontofficeBundle\Entity\Site;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\SecurityBundle\Security\BaseAccessRoleService;

class AccessRoleService extends BaseAccessRoleService
{
    public function __construct(RegistryInterface $registry, $activeBackofficeApps)
    {
        $name = 'azimut_frontoffice_roles'; //same as alias in service delaration used for ARType
        $namespace = 'Azimut\Bundle\FrontofficeBundle\Entity';
        $entities = [Site::class, Page::class];
        $roles = array('APP_FRONTOFFICE');
        $rolesOnEntities[Site::class] = [
                'VIEW',
                'EDIT_PARAMS',
                'EDIT'
        ];
        $rolesOnEntities[Page::class] = [
                'VIEW',
                'EDIT_PARAMS',
                'EDIT',
                'SUGGEST'
        ];
        parent::__construct($registry, $activeBackofficeApps, $name, $namespace, 'frontoffice', $roles, $rolesOnEntities, $entities);
    }

    public function supportsClass($class)
    {
        if (stripos($class, 'Proxies\\__CG__\\') === 0) {
            $class = substr($class, 15);
        }

        if (0 === strpos($class, Page::class)) {
            return true;
        }

        return parent::supportsClass($class);
    }
}
