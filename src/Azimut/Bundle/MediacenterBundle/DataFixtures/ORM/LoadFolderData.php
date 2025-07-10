<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-07-03
 */

namespace Azimut\Bundle\MediacenterBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\MediacenterBundle\Entity\Folder;

class LoadFolderData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $libraryFolder = $this->getReference('library');

        //Folder1 subfolders
        $subfolder1 = new Folder();
        $subfolder1->setName('My folder 1');
        $manager->persist($subfolder1);

        $subfolder2 = new Folder();
        $subfolder2->setName('My folder 2');
        $manager->persist($subfolder2);

        $subfolder1->setParentFolder($libraryFolder);
        $subfolder2->setParentFolder($libraryFolder);

        $subfolder4 = new Folder();
        $subfolder4->setName('My subfolder 1');
        $manager->persist($subfolder4);
        $subfolder4->setParentFolder($subfolder1);

        //--------------------------------------------------------------

        $subfolderAzimut = new Folder();
        $subfolderAzimut->setName('Azimut');
        $manager->persist($subfolderAzimut);
        $subfolderAzimut->setParentFolder($libraryFolder);

        $subfolderAzimutKiosks = new Folder();
        $subfolderAzimutKiosks->setName('kiosks');
        $manager->persist($subfolderAzimutKiosks);
        $subfolderAzimutKiosks->setParentFolder($subfolderAzimut);

        $subfolderAzimutWebsites = new Folder();
        $subfolderAzimutWebsites->setName('websites');
        $manager->persist($subfolderAzimutWebsites);
        $subfolderAzimutWebsites->setParentFolder($subfolderAzimut);

        $subfolderAzimutGenerals = new Folder();
        $subfolderAzimutGenerals->setName('generals');
        $manager->persist($subfolderAzimutGenerals);
        $subfolderAzimutGenerals->setParentFolder($subfolderAzimut);

        //--------------------------------------------------------------

        $manager->flush();

        $this->addReference('subfolder-1', $subfolder1);

        $this->addReference('subfolder-azimut', $subfolderAzimut);
        $this->addReference('subfolder-azimut-kiosks', $subfolderAzimutKiosks);
        $this->addReference('subfolder-azimut-websites', $subfolderAzimutWebsites);
        $this->addReference('subfolder-azimut-generals', $subfolderAzimutGenerals);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2; // order in witch files are loaded
    }
}
