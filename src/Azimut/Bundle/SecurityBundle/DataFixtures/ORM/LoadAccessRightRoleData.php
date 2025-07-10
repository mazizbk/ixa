<?php

namespace Azimut\Bundle\SecurityBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightRoles;
use Azimut\Bundle\SecurityBundle\Entity\AccessRole;
use Azimut\Bundle\SecurityBundle\Entity\AccessRightAppRoles;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\SecurityBundle\AccessRights\AccessRightService;

class LoadAccessRightRoleData extends AbstractFixture implements OrderedFixtureInterface
{
    private $accessRightService;

    public function __construct(AccessRightService $accessRightService)
    {
        $this->accessRightService = $accessRightService;
    }

    public function load(ObjectManager $manager)
    {
        // Fixtures on Global Roles
        //ADMIN given to group 'group A' USER given to group 'group B'
        //CREATE given to user 'user@user.net'

        $admin = $manager->getRepository('AzimutSecurityBundle:User')->findOneByEmail('admin@azimut.net');
        $user = $manager->getRepository('AzimutSecurityBundle:User')->findOneByEmail('harry.scott@user.net');
        $user2 = $manager->getRepository('AzimutSecurityBundle:User')->findOneByEmail('jake.wilson@user.net');
        $group1 = $manager->getRepository('AzimutSecurityBundle:Group')->findOneByName('group A');
        $group2 = $manager->getRepository('AzimutSecurityBundle:Group')->findOneByName('group B');

        $roleAdmin = new AccessRole();
        $roleAdmin->setRole('GLOBAL_ADMIN');
        $manager->persist($roleAdmin);

        $roleUser = new AccessRole();
        $roleUser->setRole('GLOBAL_USER');
        $manager->persist($roleUser);


        $roleAppDemo = new AccessRole();
        $roleAppDemo->setRole('APP_DEMO_SECURITY_INJECTION');
        $manager->persist($roleAppDemo);

        $roleAppFront = new AccessRole();
        $roleAppFront->setRole('APP_FRONTOFFICE');
        $manager->persist($roleAppFront);

        $roleAppSec = new AccessRole();
        $roleAppSec->setRole('APP_SECURITY');
        $manager->persist($roleAppSec);

        $arRolesGroup1 = new AccessRightRoles();
        $arRolesGroup1
            ->addRole($roleAdmin)
            ->setGroup($group1)
        ;

        $group1->addAccessRight($arRolesGroup1);

        $arRolesGroup2 = new AccessRightRoles();
        $arRolesGroup2
            ->addRole($roleUser)
            ->setGroup($group2)
        ;

        $group2->addAccessRight($arRolesGroup2);

        $arRolesAppDemo = new AccessRightAppRoles();
        $arRolesAppDemo
            ->addRole($roleAppSec)
            ->addRole($roleAppFront)
            ->setUser($user)
        ;

        $user->addAccessRight($arRolesAppDemo);

        $manager->persist($arRolesGroup1);
        $manager->persist($arRolesGroup2);
        $manager->persist($arRolesAppDemo);

        $arRolesAppGroup = new AccessRightAppRoles();
        $arRolesAppGroup
            ->addRole($roleAppSec)
            ->setGroup($group1)
        ;
        $manager->persist($arRolesAppGroup);

        $manager->flush();
        //  [Exception]  Global Role Not Found
        // $artest = $this->accessRightService->addAccessRight($admin, 'PASTE');
    }

    public function getOrder()
    {
        return 10;
    }
}
