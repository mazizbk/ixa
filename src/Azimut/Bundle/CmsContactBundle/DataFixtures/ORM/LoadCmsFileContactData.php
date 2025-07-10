<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-12-05 11:11:03
 */

namespace Azimut\Bundle\CmsContactBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Azimut\Bundle\CmsContactBundle\Entity\CmsFileContact;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCmsFileContactData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $contact1 = new CmsFileContact();
        $contact1
            ->setFirstName('Martin')
            ->setLastName('Boucher')
            ->setAddress('42 avenue Georges')
            ->setZipCode('55555')
            ->setCity('Paris')
            ->setCountry('France')
            ->setPhone('1111111111')
            ->setEmail('john@adipisicing.com')
        ;
        $manager->persist($contact1);

        $contact2 = new CmsFileContact();
        $contact2
            ->setFirstName('Kathleen')
            ->setLastName('Gibson')
            ->setAddress('2 Omnis')
            ->setZipCode('3463')
            ->setCity('New York')
            ->setCountry('USA')
            ->setPhone('2323232323')
            ->setEmail('hector@eiusmod.com')
        ;
        $manager->persist($contact2);

        $manager->flush();

        $this->addReference('cms-contact1', $contact1);
        $this->addReference('cms-contact2', $contact2);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 5; // order in witch files are loaded
    }
}
