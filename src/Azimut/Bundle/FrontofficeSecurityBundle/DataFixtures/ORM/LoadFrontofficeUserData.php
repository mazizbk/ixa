<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-05-04 14:23:33
 */

namespace Azimut\Bundle\FrontofficeSecurityBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\FrontofficeSecurityBundle\Entity\FrontofficeUser;

class LoadFrontofficeUserData extends AbstractFixture
{
    public function load(ObjectManager $manager)
    {
        $user = new FrontofficeUser();
        $user
            ->setEmail('demo.front.user@azimut.net')
            ->setPlainPassword('user')
            ->setFirstName('Demo')
            ->setLastName('Front User')
        ;
        $manager->persist($user);

        $manager->flush();
    }
}
