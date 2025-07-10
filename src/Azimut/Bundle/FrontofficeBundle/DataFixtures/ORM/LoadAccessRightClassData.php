<?php

namespace Azimut\Bundle\FrontofficeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\SecurityBundle\Services\UserManager;
use Azimut\Bundle\SecurityBundle\AccessRights\AccessRightService;

class LoadAccessRightClassData extends AbstractFixture implements OrderedFixtureInterface
{
    private $userManager;

    private $accessRightService;

    public function __construct(UserManager $userManager, AccessRightService $accessRightService)
    {
        $this->userManager = $userManager;
        $this->accessRightService = $accessRightService;
    }

    public function load(ObjectManager $manager)
    {
        $admin = $this->userManager->findUserByUsername('admin@azimut.net');
        $user = $this->userManager->findUserByUsername('harry.scott@user.net');
        $test = $this->userManager->findUserByUsername('jake.wilson@user.net');

        $artest = $this->accessRightService->addAccessRight($admin, 'VIEW', 'Azimut\Bundle\FrontofficeBundle\Entity\Site');

        $artest = $this->accessRightService->addAccessRight($user, 'EDIT', 'Azimut\Bundle\MediacenterBundle\Entity\Media');

        $artest = $this->accessRightService->addAccessRight($test, 'VIEW', 'Azimut\Bundle\FrontofficeBundle\Entity\Page');

            //  [Exception] Class doesn't support AccessRight
            // $artest = $this->accessRightService->addAccessRight($test, 'VIEW', 'Azimut\Bundle\MediacenterBundle\Entity\Menu');

            //  [Exception] Class doesn't exist
            // $artest = $this->accessRightService->addAccessRight($test, 'VIEW', 'Azimut\Bundle\FrontofficeBundle\Entity\Article');

            //  [Exception]  Role on this class not supported
            // $artest = $this->accessRightService->addAccessRight($admin, 'CREATE', 'Azimut\Bundle\FrontofficeBundle\Entity\Site');
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 9; // l'ordre dans lequel les fichiers sont chargés
    }
}
