<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-10-23 15:57:39
 */

namespace Azimut\Bundle\SecurityBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\SecurityBundle\Entity\User;
use Azimut\Bundle\SecurityBundle\Services\UserManager;
use Azimut\Bundle\SecurityBundle\Services\GroupManager;

class LoadUserData extends AbstractFixture implements OrderedFixtureInterface
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
        // Fixtures for users and group
        // 2 users: admin@azimut.net
        // 2 groups: administrators, users

        $user = $this->userManager->createUser();
        $user->setUsername('harry.scott@user.net');
        $user->setFirstName('Harry');
        $user->setLastName('Scott');
        $user->setEmail('harry.scott@user.net');
        $user->setPassword('user');
        $user->setOauthId('2');
        $this->userManager->updateUser($user);

        $user2 = $this->userManager->createUser();
        $user2->setUsername('jake.wilson@user.net');
        $user2->setFirstName('Jake');
        $user2->setLastName('Wilson');
        $user2->setEmail('jake.wilson@user.net');
        $user2->setPassword('user');
        $user2->setOauthId('3');
        $this->userManager->updateUser($user2);

        $groupA = $this->groupManager->createGroup();
        $groupA->setName('group A');

        $this->groupManager->updateGroup($groupA);

        $groupB = $this->groupManager->createGroup();
        $groupB->setName('group B');
        $user->addGroup($groupB);
        $user2->addGroup($groupB);

        $this->groupManager->updateGroup($groupB);
    }

    public function getOrder()
    {
        return 5; // l'ordre dans lequel les fichiers sont chargés
    }
}
