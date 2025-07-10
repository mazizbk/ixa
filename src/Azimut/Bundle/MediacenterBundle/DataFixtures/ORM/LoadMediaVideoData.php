<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-07-03
 */

namespace Azimut\Bundle\MediacenterBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\MediacenterBundle\Entity\MediaVideo;
use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationVideo;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadMediaVideoData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    private $container;

    private $fixturesFolder;

    /**
     * {@inheritDoc}
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
        $this->fixturesFolder = $this->container->get('kernel')->locateResource('@AzimutMediacenterBundle/Resources/fixtures');
    }

    /**
     * {@inheritDoc}
     */
    public function load(ObjectManager $manager)
    {
        $video = new MediaVideo();
        $video
            ->setName('Media video test 1')
            ->setDescription('My video description 1', 'en')
            ->setDescription('Ma description de vidéo 1', 'fr')
            ->setCopyright('Robert Ipsum')
            ->setFolder($this->getReference('subfolder-1'))
        ;

        $manager->persist($video);

        $video2 = new MediaVideo();
        $video2
            ->setName('Media video test 2 - youtube')
            ->setDescription('My video description 2', 'en')
            ->setDescription('Ma description de vidéo 2', 'fr')
            ->setCopyright('Robert Ipsum')
            ->setFolder($this->getReference('subfolder-1'))
        ;

        $manager->persist($video2);

        //declinations
        $videoDeclination2 = new MediaDeclinationVideo();
        $videoDeclination2
            ->setName('My youtube video')
            ->setEmbedHtml('<iframe width="420" height="315" src="//www.youtube.com/embed/QH2-TGUlwu4" frameborder="0" allowfullscreen></iframe>')
            ->setMedia($video2)
        ;
        $manager->persist($videoDeclination2);

        $this->addReference('video_declination-2', $videoDeclination2);

        $video3 = new MediaVideo();
        $video3
            ->setName('Media video test 3')
            ->setDescription('My video description 2', 'en')
            ->setDescription('Ma description de vidéo 2', 'fr')
            ->setCopyright('Robert Ipsum')
            ->setFolder($this->getReference('subfolder-1'))
        ;

        $manager->persist($video3);

        //declinations
        $file = $this->getFixtureFile("video1.mp4");
        $videoDeclination3 = new MediaDeclinationVideo();
        $videoDeclination3
            ->setName('My mp4 video')
            ->setMedia($video3)
            ->setFile($file)
        ;
        $manager->persist($videoDeclination3);

        $this->addReference('video_declination-3', $videoDeclination3);

        $manager->flush();
    }

    protected function getFixtureFile($fileName)
    {
        $originalFilePath = $this->fixturesFolder."/$fileName";
        $filePath = $this->fixturesFolder."/fixture-$fileName";
        copy($originalFilePath, $filePath);

        return new UploadedFile($filePath, $fileName, null, null, null, true);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 3; // order in witch files are loaded
    }
}
