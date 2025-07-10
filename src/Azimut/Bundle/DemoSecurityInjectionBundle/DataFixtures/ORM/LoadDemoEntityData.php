<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-05-21 10:39:39
*/

namespace Azimut\Bundle\DemoSecurityInjectionBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\DemoSecurityInjectionBundle\Entity\DemoEntity;

class LoadDemoEntityData extends AbstractFixture
{
    /**
    * {@inheritDoc}
    */
    public function load(ObjectManager $manager)
    {
        $entity = new DemoEntity('1');
        $manager->persist($entity);

        $entity = new DemoEntity('2');
        $manager->persist($entity);

        $manager->flush();
    }
}
