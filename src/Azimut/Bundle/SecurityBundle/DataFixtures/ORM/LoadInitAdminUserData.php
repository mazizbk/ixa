<?php
/**
 * @author: Florentin Stéphane
 * date:   2018-11
 */

namespace Azimut\Bundle\SecurityBundle\DataFixtures\ORM;

use Azimut\Bundle\SecurityBundle\Entity\AccessRightRoles;
use Azimut\Bundle\SecurityBundle\Entity\AccessRole;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\SecurityBundle\Entity\User;
use Azimut\Bundle\SecurityBundle\Services\UserManager;

class LoadInitAdminUserData extends AbstractFixture implements OrderedFixtureInterface
{
    private $userManager;

    public function __construct(UserManager $userManager)
    {
        $this->userManager = $userManager;
    }

    public function load(ObjectManager $manager)
    {
        $admin = $this->userManager->createUser();
        $admin->setUsername('admin@azimut.net');
        $admin->setFirstName('Azimut');
        $admin->setLastName('Communication');
        $admin->setEmail('admin@azimut.net');
        $admin->setPassword('admin');
        $admin->setOauthId('1');
        $this->userManager->updateUser($admin);

        $roleSuperAdmin = new AccessRole();
        $roleSuperAdmin->setRole('SUPER_ADMIN');
        $manager->persist($roleSuperAdmin);

        $arRolesSuperAdmin = new AccessRightRoles();
        $arRolesSuperAdmin
            ->addRole($roleSuperAdmin)
            ->setUser($admin)
        ;
        $admin->addAccessRight($arRolesSuperAdmin);

        $manager->persist($arRolesSuperAdmin);

        $manager->flush();
    }

    public function getOrder()
    {
        return 1; // l'ordre dans lequel les fichiers sont chargés
    }
}
