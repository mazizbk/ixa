<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2013-07-03
 */

namespace Azimut\Bundle\MediacenterBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\MediacenterBundle\Entity\MediaImage;
use Azimut\Bundle\MediacenterBundle\Entity\MediaDeclinationImage;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class LoadMediaImageData extends AbstractFixture implements OrderedFixtureInterface, ContainerAwareInterface
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
        $image = new MediaImage();
        $image
            ->setName('Media image test 1')
            ->setDescription("My image description 1", 'en')
            ->setDescription("Ma description d'image 1", 'fr')
            ->setAltText('this is an alternate text', 'en')
            ->setAltText('ceci est un texte alternatif', 'fr')
            ->setFolder($this->getReference('subfolder-1'))
        ;

        $manager->persist($image);

        //declinations
        $imageDeclination1 = new MediaDeclinationImage();
        $file = $this->getFixtureFile("img1.jpg");
        $imageDeclination1
            ->setName("My jpg image")
            ->setPixelWidth(2000)
            ->setPixelHeight(1500)
            ->setMedia($image)
            ->setFile($file)
        ;
        $manager->persist($imageDeclination1);

        $image2 = new MediaImage();
        $image2
            ->setName('Media image test 2')
            ->setDescription("My image description 2", 'en')
            ->setDescription("Ma description d'image 2", 'fr')
            ->setAltText('this is an alternate text 2', 'en')
            ->setAltText('ceci est un texte alternatif 2', 'fr')
            ->setFolder($this->getReference('subfolder-1'))
        ;

        $manager->persist($image2);

        //declinations
        $imageDeclination2 = new MediaDeclinationImage();
        $file = $this->getFixtureFile("img2.jpg");
        $imageDeclination2
            ->setName("My png image")
            ->setPixelWidth(200)
            ->setPixelHeight(150)
            ->setMedia($image2)
            ->setFile($file)
        ;
        $manager->persist($imageDeclination2);

        $manager->flush();

        $image3 = new MediaImage();
        $image3
            ->setName('Media image test 3')
            ->setDescription("My image description 3", 'en')
            ->setDescription("Ma description d'image 3", 'fr')
            ->setAltText('this is an alternate text 3', 'en')
            ->setAltText('ceci est un texte alternatif 3', 'fr')
            ->setFolder($this->getReference('subfolder-1'))
        ;

        $manager->persist($image3);

        //declinations
        $imageDeclination3 = new MediaDeclinationImage();
        $file = $this->getFixtureFile("img4.jpg");
        $imageDeclination3
            ->setName("My gif image")
            ->setPixelWidth(200)
            ->setPixelHeight(150)
            ->setMedia($image3)
            ->setFile($file)
        ;
        $manager->persist($imageDeclination3);

        //--------------------------------------------------------------

        $azimutLogo = new MediaImage();
        $azimutLogo
            ->setName('Logo')
            ->setDescription("Azimut's logo", 'en')
            ->setDescription("Le logo d'Azimut", 'fr')
            ->setAltText('azimut logo', 'en')
            ->setAltText('logo azimut', 'fr')
            ->setFolder($this->getReference('subfolder-azimut'))
        ;

        $manager->persist($azimutLogo);

        $azimutLogoPng = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/logo-azimut.png");
        $azimutLogoPng
            ->setName("Logo")
            ->setPixelWidth(1145)
            ->setPixelHeight(311)
            ->setMedia($azimutLogo)
            ->setFile($file)
        ;
        $manager->persist($azimutLogoPng);

        $azimutLogoPngInverse = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/logo-azimut-inverse.png");
        $azimutLogoPngInverse
            ->setName("Logo inverse")
            ->setPixelWidth(1145)
            ->setPixelHeight(311)
            ->setMedia($azimutLogo)
            ->setFile($file)
        ;
        $manager->persist($azimutLogoPngInverse);

        $azimutLogoAi = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/logo-azimut.ai");
        $azimutLogoAi
            ->setName("Logo AI")
            ->setMedia($azimutLogo)
            ->setFile($file)
        ;
        $manager->persist($azimutLogoAi);

        $azimutLogoEps = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/logo-azimut.eps");
        $azimutLogoEps
            ->setName("Logo EPS")
            ->setMedia($azimutLogo)
            ->setFile($file)
        ;
        $manager->persist($azimutLogoEps);



        $azimutKioskArbreCommunication = new MediaImage();
        $azimutKioskArbreCommunication
            ->setName('Arbre de communication')
            ->setDescription("Arbre de communication", 'en')
            ->setDescription("Communication tree", 'fr')
            ->setAltText('azimut interactive kiosk - communication tree', 'en')
            ->setAltText('borne interactive azimut - arbre de communication', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-kiosks'))
        ;

        $manager->persist($azimutKioskArbreCommunication);

        $azimutKioskArbreCommunicationDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/kiosks/arbre-communication.png");
        $azimutKioskArbreCommunicationDeclination
            ->setName("Original")
            ->setMedia($azimutKioskArbreCommunication)
            ->setFile($file)
        ;
        $manager->persist($azimutKioskArbreCommunicationDeclination);

        $azimutKioskDesign = new MediaImage();
        $azimutKioskDesign
            ->setName('Design')
            ->setDescription("Design kiosk", 'en')
            ->setDescription("Borne Design", 'fr')
            ->setAltText('azimut interactive kiosk - design', 'en')
            ->setAltText('borne interactive azimut - design', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-kiosks'))
        ;

        $manager->persist($azimutKioskDesign);

        $azimutKioskDesignDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/kiosks/design.png");
        $azimutKioskDesignDeclination
            ->setName("Original")
            ->setMedia($azimutKioskDesign)
            ->setFile($file)
        ;
        $manager->persist($azimutKioskDesignDeclination);

        $azimutKioskEasyTouchUp = new MediaImage();
        $azimutKioskEasyTouchUp
            ->setName('Easy Touch Up')
            ->setDescription("Easy Touch Up kiosk", 'en')
            ->setDescription("Borne Easy Touch Up", 'fr')
            ->setAltText('azimut interactive kiosk - easy touch up', 'en')
            ->setAltText('borne interactive azimut - easy touch up', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-kiosks'))
        ;

        $manager->persist($azimutKioskEasyTouchUp);

        $azimutKioskEasyTouchUpDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/kiosks/easy-touch-up.png");
        $azimutKioskEasyTouchUpDeclination
            ->setName("Original")
            ->setMedia($azimutKioskEasyTouchUp)
            ->setFile($file)
        ;
        $manager->persist($azimutKioskEasyTouchUpDeclination);

        $azimutKioskElite = new MediaImage();
        $azimutKioskElite
            ->setName('E-lite')
            ->setDescription("E-lite kiosk", 'en')
            ->setDescription("Borne E-lite", 'fr')
            ->setAltText('azimut interactive kiosk - e-lite', 'en')
            ->setAltText('borne interactive azimut - e-lite', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-kiosks'))
        ;

        $manager->persist($azimutKioskElite);

        $azimutKioskEliteDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/kiosks/e-lite.png");
        $azimutKioskEliteDeclination
            ->setName("Original")
            ->setMedia($azimutKioskElite)
            ->setFile($file)
        ;
        $manager->persist($azimutKioskEliteDeclination);

        $azimutKioskKiosqueUniversel = new MediaImage();
        $azimutKioskKiosqueUniversel
            ->setName('Kiosque Universel')
            ->setDescription("Kiosque Universel kiosk", 'en')
            ->setDescription("Borne Kiosque Universel", 'fr')
            ->setAltText('azimut interactive kiosk - kiosque universel', 'en')
            ->setAltText('borne interactive azimut - kiosque universel', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-kiosks'))
        ;

        $manager->persist($azimutKioskKiosqueUniversel);

        $azimutKioskKiosqueUniverselDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/kiosks/kiosque-universel.png");
        $azimutKioskKiosqueUniverselDeclination
            ->setName("Original")
            ->setMedia($azimutKioskKiosqueUniversel)
            ->setFile($file)
        ;
        $manager->persist($azimutKioskKiosqueUniverselDeclination);

        $azimutKioskMuseo = new MediaImage();
        $azimutKioskMuseo
            ->setName('Museo')
            ->setDescription("Museo kiosk", 'en')
            ->setDescription("Borne Museo", 'fr')
            ->setAltText('azimut interactive kiosk - museo', 'en')
            ->setAltText('borne interactive azimut - museo', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-kiosks'))
        ;

        $manager->persist($azimutKioskMuseo);

        $azimutKioskMuseoDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/kiosks/museo.png");
        $azimutKioskMuseoDeclination
            ->setName("Original")
            ->setMedia($azimutKioskMuseo)
            ->setFile($file)
        ;
        $manager->persist($azimutKioskMuseoDeclination);

        $azimutKioskTable = new MediaImage();
        $azimutKioskTable
            ->setName('Table')
            ->setDescription("Table kiosk", 'en')
            ->setDescription("Borne Table", 'fr')
            ->setAltText('azimut interactive kiosk - table', 'en')
            ->setAltText('borne interactive azimut - table', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-kiosks'))
        ;

        $manager->persist($azimutKioskTable);

        $azimutKioskTableDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/kiosks/table.png");
        $azimutKioskTableDeclination
            ->setName("Original")
            ->setMedia($azimutKioskTable)
            ->setFile($file)
        ;
        $manager->persist($azimutKioskTableDeclination);

        $azimutKioskTotemPlexy = new MediaImage();
        $azimutKioskTotemPlexy
            ->setName('Totem plexy')
            ->setDescription("Totem kiosk", 'en')
            ->setDescription("Borne Totem", 'fr')
            ->setAltText('azimut interactive kiosk - totem', 'en')
            ->setAltText('borne interactive azimut - totem', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-kiosks'))
        ;

        $manager->persist($azimutKioskTotemPlexy);

        $azimutKioskTotemPlexyDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/kiosks/totem.png");
        $azimutKioskTotemPlexyDeclination
            ->setName("Original")
            ->setMedia($azimutKioskTotemPlexy)
            ->setFile($file)
        ;
        $manager->persist($azimutKioskTotemPlexyDeclination);

        $azimutKioskTotem = new MediaImage();
        $azimutKioskTotem
            ->setName('Totem')
            ->setDescription("Totem kiosk", 'en')
            ->setDescription("Borne Totem", 'fr')
            ->setAltText('azimut interactive kiosk - totem', 'en')
            ->setAltText('borne interactive azimut - totem', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-kiosks'))
        ;

        $manager->persist($azimutKioskTotem);

        $azimutKioskTotemDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/kiosks/totem2.png");
        $azimutKioskTotemDeclination
            ->setName("Original")
            ->setMedia($azimutKioskTotem)
            ->setFile($file)
        ;
        $manager->persist($azimutKioskTotemDeclination);

        $azimutKioskTouchscreen = new MediaImage();
        $azimutKioskTouchscreen
            ->setName('Touchscreen')
            ->setDescription("Touchscreen", 'en')
            ->setDescription("Ecran tactile", 'fr')
            ->setAltText('azimut interactive kiosk - touchscreen', 'en')
            ->setAltText('borne interactive azimut - écran tactile', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-kiosks'))
        ;

        $manager->persist($azimutKioskTouchscreen);

        $azimutKioskTouchscreenDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/kiosks/touchscreen.png");
        $azimutKioskTouchscreenDeclination
            ->setName("Original")
            ->setMedia($azimutKioskTouchscreen)
            ->setFile($file)
        ;
        $manager->persist($azimutKioskTouchscreenDeclination);

        $azimutKioskUrbana = new MediaImage();
        $azimutKioskUrbana
            ->setName('Urbana')
            ->setDescription("Urbana kiosk", 'en')
            ->setDescription("Borne Urbana", 'fr')
            ->setAltText('azimut interactive kiosk - urbana', 'en')
            ->setAltText('borne interactive azimut - urbana', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-kiosks'))
        ;

        $manager->persist($azimutKioskUrbana);

        $azimutKioskUrbanaDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/kiosks/urbana.png");
        $azimutKioskUrbanaDeclination
            ->setName("Original")
            ->setMedia($azimutKioskUrbana)
            ->setFile($file)
        ;
        $manager->persist($azimutKioskUrbanaDeclination);

        $azimutKioskVentana = new MediaImage();
        $azimutKioskVentana
            ->setName('Ventana')
            ->setDescription("Ventana kiosk", 'en')
            ->setDescription("Borne Ventana", 'fr')
            ->setAltText('azimut interactive kiosk - ventana', 'en')
            ->setAltText('borne interactive azimut - ventana', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-kiosks'))
        ;

        $manager->persist($azimutKioskVentana);

        $azimutKioskVentanaDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/kiosks/ventana.png");
        $azimutKioskVentanaDeclination
            ->setName("Original")
            ->setMedia($azimutKioskVentana)
            ->setFile($file)
        ;
        $manager->persist($azimutKioskVentanaDeclination);


        $azimutWebsite1 = new MediaImage();
        $azimutWebsite1
            ->setName('Asso Eric Tabarly')
            ->setDescription("Eric Tabarly's association website", 'en')
            ->setDescription("Site internet de l'association Eric Tabarly", 'fr')
            ->setAltText('azimut website - eric tabarly association', 'en')
            ->setAltText('site internet azimut - eric tabarly association', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-websites'))
        ;

        $manager->persist($azimutWebsite1);

        $azimutWebsite1Declination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/websites/asso-tabarly.png");
        $azimutWebsite1Declination
            ->setName("Original")
            ->setMedia($azimutWebsite1)
            ->setFile($file)
        ;
        $manager->persist($azimutWebsite1Declination);

        $azimutWebsite2 = new MediaImage();
        $azimutWebsite2
            ->setName('Cycles Marcarini')
            ->setDescription("Marcarini Cycles website", 'en')
            ->setDescription("Site internet des Cycles Marcarini", 'fr')
            ->setAltText('azimut website - cycles marcarini', 'en')
            ->setAltText('site internet azimut - cycles marcarini', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-websites'))
        ;

        $manager->persist($azimutWebsite2);

        $azimutWebsite2Declination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/websites/cycles-marcarini.jpg");
        $azimutWebsite2Declination
            ->setName("Original")
            ->setMedia($azimutWebsite2)
            ->setFile($file)
        ;
        $manager->persist($azimutWebsite2Declination);

        $azimutWebsite3 = new MediaImage();
        $azimutWebsite3
            ->setName('Djeep')
            ->setDescription("Djeep lighter's website", 'en')
            ->setDescription("Site internet des briquets Djeep", 'fr')
            ->setAltText('azimut website - djeep lighters', 'en')
            ->setAltText('site internet azimut - briquets djeep', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-websites'))
        ;

        $manager->persist($azimutWebsite3);

        $azimutWebsite3Declination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/websites/djeep.jpg");
        $azimutWebsite3Declination
            ->setName("Original")
            ->setMedia($azimutWebsite3)
            ->setFile($file)
        ;
        $manager->persist($azimutWebsite3Declination);

        $azimutWebsite4 = new MediaImage();
        $azimutWebsite4
            ->setName("Europa Warm'Up")
            ->setDescription("Europa Warm'Up's website", 'en')
            ->setDescription("Site internet de l'Europa Warm'Up", 'fr')
            ->setAltText("azimut website - europa warm'up", 'en')
            ->setAltText("site internet azimut - europa warm'up", 'fr')
            ->setFolder($this->getReference('subfolder-azimut-websites'))
        ;

        $manager->persist($azimutWebsite4);

        $azimutWebsite4Declination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/websites/europa-warm-up.jpg");
        $azimutWebsite4Declination
            ->setName("Original")
            ->setMedia($azimutWebsite4)
            ->setFile($file)
        ;
        $manager->persist($azimutWebsite4Declination);

        $azimutWebsite5 = new MediaImage();
        $azimutWebsite5
            ->setName('La Thalassa')
            ->setDescription("La Thalassa's website", 'en')
            ->setDescription('Site internet de La Thalassa', 'fr')
            ->setAltText('azimut website - la thalassa', 'en')
            ->setAltText('site internet azimut - la thalassa', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-websites'))
        ;

        $manager->persist($azimutWebsite5);

        $azimutWebsite5Declination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/websites/la-thalassa.jpg");
        $azimutWebsite5Declination
            ->setName("Original")
            ->setMedia($azimutWebsite5)
            ->setFile($file)
        ;
        $manager->persist($azimutWebsite5Declination);

        $azimutWebsite6 = new MediaImage();
        $azimutWebsite6
            ->setName('Oceans Evasion')
            ->setDescription("Oceans Evasion's website", 'en')
            ->setDescription("Site internet d'Oceans Evasion", 'fr')
            ->setAltText('azimut website - oceans évasion', 'en')
            ->setAltText('site internet azimut - oceans évasion', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-websites'))
        ;

        $manager->persist($azimutWebsite6);

        $azimutWebsite6Declination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/websites/oceans-evasion.jpg");
        $azimutWebsite6Declination
            ->setName("Original")
            ->setMedia($azimutWebsite6)
            ->setFile($file)
        ;
        $manager->persist($azimutWebsite6Declination);

        $azimutWebsite7 = new MediaImage();
        $azimutWebsite7
            ->setName('Volvo Ocean Race Lorient')
            ->setDescription("Volvo Ocean Race Lorient's website", 'en')
            ->setDescription("Site internet de la Volvo Ocean Race Lorient", 'fr')
            ->setAltText('azimut website - volvo ocean race lorient', 'en')
            ->setAltText('site internet azimut - volvo ocean race lorient', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-websites'))
        ;

        $manager->persist($azimutWebsite7);

        $azimutWebsite7Declination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/websites/volvo-ocean-race-lorient.png");
        $azimutWebsite7Declination
            ->setName("Original")
            ->setMedia($azimutWebsite7)
            ->setFile($file)
        ;
        $manager->persist($azimutWebsite7Declination);

        $azimutGeneralPatchwork = new MediaImage();
        $azimutGeneralPatchwork
            ->setName('Patchwork')
            ->setDescription("Patchwork", 'en')
            ->setDescription("Patchwork", 'fr')
            ->setAltText('interactive-kiosk-website-design', 'en')
            ->setAltText('borne-interactive-création-site-internet', 'fr')
            ->setFolder($this->getReference('subfolder-azimut-generals'))
        ;

        $manager->persist($azimutGeneralPatchwork);

        $azimutGeneralPatchworkDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("azimut/generals/patchwork.jpg");
        $azimutGeneralPatchworkDeclination
            ->setName("Original")
            ->setMedia($azimutGeneralPatchwork)
            ->setFile($file)
        ;
        $manager->persist($azimutGeneralPatchworkDeclination);


        $shoesImage = new MediaImage();
        $shoesImage
            ->setName('Shoes')
            ->setDescription("Shoes", 'en')
            ->setDescription("Chaussures", 'fr')
            ->setAltText('shoes', 'en')
            ->setAltText('chaussures', 'fr')
            ->setFolder($this->getReference('subfolder-1'))
        ;

        $manager->persist($shoesImage);

        $shoesImageDeclination = new MediaDeclinationImage();
        $file = $this->getFixtureFile("shoes.jpg");
        $shoesImageDeclination
            ->setName("Original")
            ->setMedia($shoesImage)
            ->setFile($file)
        ;
        $manager->persist($shoesImageDeclination);


        //--------------------------------------------------------------


        $manager->flush();

        $this->addReference('image_declination-1', $imageDeclination1);
        $this->addReference('image_declination-2', $imageDeclination2);
        $this->addReference('image_declination-3', $imageDeclination3);

        $this->addReference('image_declination-kiosks-arbre-communication', $azimutKioskArbreCommunicationDeclination);
        $this->addReference('image_declination-kiosks-design', $azimutKioskDesignDeclination);
        $this->addReference('image_declination-kiosks-easy-touch-up', $azimutKioskEasyTouchUpDeclination);
        $this->addReference('image_declination-kiosks-elite', $azimutKioskEliteDeclination);
        $this->addReference('image_declination-kiosks-kiosque-universel', $azimutKioskKiosqueUniverselDeclination);
        $this->addReference('image_declination-kiosks-museo', $azimutKioskMuseoDeclination);
        $this->addReference('image_declination-kiosks-table', $azimutKioskTableDeclination);
        $this->addReference('image_declination-kiosks-totem-plexy', $azimutKioskTotemPlexyDeclination);
        $this->addReference('image_declination-kiosks-totem', $azimutKioskTotemDeclination);
        $this->addReference('image_declination-kiosks-touchscreen', $azimutKioskTouchscreenDeclination);
        $this->addReference('image_declination-kiosks-urbana', $azimutKioskUrbanaDeclination);
        $this->addReference('image_declination-kiosks-ventana', $azimutKioskVentanaDeclination);
        $this->addReference('image_declination-general-patchwork', $azimutGeneralPatchworkDeclination);

        $this->addReference('image_declination-shoes', $shoesImageDeclination);
    }

    protected function getFixtureFile($fileName)
    {
        $originalFilePath = $this->fixturesFolder."/$fileName";
        $filePath = $this->fixturesFolder."/$fileName.tmp";
        copy($originalFilePath, $filePath);

        return new UploadedFile($filePath, $fileName, null, null, null, true);
    }

    /**
     * {@inheritDoc}
     */
    public function getOrder()
    {
        return 2; // order in witch files are loaded
    }
}
