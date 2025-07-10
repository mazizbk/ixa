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

class LoadInitFolderData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $libraryFolder = new Folder();
        $libraryFolder->setName('My.library');
        $manager->persist($libraryFolder);

        $submittedLibraryFolder = new Folder();
        $submittedLibraryFolder->setName('Submitted.library');
        $manager->persist($submittedLibraryFolder);

        $manager->flush();

        $this->addReference('library', $libraryFolder);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 1; // order in witch files are loaded
    }
}
