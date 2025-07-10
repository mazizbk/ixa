<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-01-27 14:08:34
 */

namespace Azimut\Bundle\MediacenterBundle\Security;

use Azimut\Bundle\MediacenterBundle\Entity\Folder;
use Azimut\Bundle\MediacenterBundle\Entity\Media;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Azimut\Bundle\SecurityBundle\Security\BaseAccessRoleService;

class AccessRoleService extends BaseAccessRoleService
{
    public function __construct(RegistryInterface $registry, $activeBackofficeApps)
    {
        $name = 'azimut_mediacenter_roles'; //same as alias in service declaration used for ARType
        $roles = array('APP_MEDIACENTER');
        $namespace = 'Azimut\Bundle\MediacenterBundle\Entity';
        $entities = [Folder::class, Media::class];
        $rolesOnEntities[Folder::class] = [
            'VIEW',
            'WRITE'
        ];
        $rolesOnEntities[Media::class] = [
            'VIEW',
            'EDIT'
        ];
        parent::__construct($registry, $activeBackofficeApps, $name, $namespace, 'mediacenter', $roles, $rolesOnEntities, $entities);
    }
}
