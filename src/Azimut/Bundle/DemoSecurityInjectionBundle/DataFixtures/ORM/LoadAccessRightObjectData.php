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
        // Do stuff
    }

    public function getOrder()
    {
        return 16; // has to be necessarily after loading the users
    }
}
