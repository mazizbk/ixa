<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-05 11:11:03
 */

namespace Azimut\Bundle\ModerationBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\ModerationBundle\Entity\CmsFileArticleBuffer;

class LoadCmsFileArticleBufferData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $articleBuffer1 = new CmsFileArticleBuffer();
        $articleBuffer1
            ->setLocale('fr')
            ->setUserEmail('demouser@demo.com')
            ->setUserIp('0.0.0.0')
            ->setDomainName('demo@demo.com')
            ->setUserLocale('en')
        ;
        $articleBuffer1->title = 'my submitted article 1';
        $articleBuffer1->author = 'Robert Ipsum';
        $articleBuffer1->text = 'Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.';
        $manager->persist($articleBuffer1);

        $manager->flush();
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 10; // order in witch files are loaded
    }
}
