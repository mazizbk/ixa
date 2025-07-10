<?php

namespace Azimut\Bundle\FrontofficeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\FrontofficeBundle\Entity\AccessRightSite;
use Azimut\Bundle\SecurityBundle\Services\UserManager;
use Azimut\Bundle\SecurityBundle\Services\GroupManager;

class LoadAccessRightObjectData extends AbstractFixture implements OrderedFixtureInterface
{
    private $userManager;

    private $groupManager;

    public function __construct(UserManager $userManager, GroupManager $groupManager)
    {
        $this->userManager = $userManager;
        $this->groupManager = $groupManager;
    }

    public function load(ObjectManager $manager)
    {
        $admin = $this->userManager->findUserByUsername('admin@azimut.net');
        $user = $this->userManager->findUserByUsername('harry.scott@user.net');
        $user2 = $this->userManager->findUserByUsername('jake.wilson@user.net');

        $groupA = $this->groupManager->findGroupByName('administrators');
/*
        $site = $this->getReference('site1');
      //  $menu1 = $this->getReference('menu1');
      //  $menu2 = $this->getReference('menu2');
        $page = $this->getReference('page1');

        $arService = $this->container->get('azimut_security.access_right_service');

        $artest = $arService->addAccessRight($user, 'EDIT', $site);
        // Role added on same AccessRightSite
    //    $artest = $arService->addAccessRight($admin, 'DELETE', $site);

    //    $artest = $arService->addAccessRight($admin, 'CREATE', $menu1);

        $artest = $arService->addAccessRight($user, 'VIEW', $page);

       /* $arService->removeAccessRight($admin, 'EDIT', $site);
        $arService->removeAccessRight($admin, 'DELETE', $site);
        $manager->flush(); */
        // [Exception] Role On this Object Not Supported
        // $artest = $arService->addAccessRight($admin, 'CREATE', $site);

        // [Exception] Role On this Object Not Supported
        // $artest = $arService->addAccessRight($admin, 'ADD', $menu1);

        // [Exception] Role On this Object Not Supported
        //$artest = $arService->addAccessRight($admin, 'ADD', $page);
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 7; // l'ordre dans lequel les fichiers sont chargés
    }
}
