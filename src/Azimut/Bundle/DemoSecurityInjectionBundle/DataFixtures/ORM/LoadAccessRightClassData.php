<?php
/*
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-21 11:19:39
 */

namespace Azimut\Bundle\DemoSecurityInjectionBundle\DataFixtures\ORM;

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
        // Do stuff
    }

    public function getOrder()
    {
        return 15;
    }
}
