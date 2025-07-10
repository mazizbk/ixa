<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2014-02-07 15:53:59
 */

namespace Azimut\Bundle\FrontofficeBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Azimut\Bundle\FrontofficeBundle\Entity\Repository\ZoneRepository;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Doctrine\Common\Persistence\ObjectManager;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneCmsFileAttachment;

class LoadZoneCmsFileAttachmentData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
    * {@inheritDoc}
    */
    public function load(ObjectManager $objectManager)
    {
        $this->objectManager = $objectManager;

        //--------------------------------------------------------
        // Demo site

        $this->addCmsFileToZone('cms-article1', 'page1', 'left', 1);
        $this->addCmsFileToZone('cms-article2', 'page1', 'left', 2);

        $this->addCmsFileToZone('cms-article3', 'page1', 'center', 1);
        $this->addCmsFileToZone('cms-article4', 'page1', 'center', 2);

        $this->addCmsFileToZone('cms-contact1', 'page1', 'right', 1);
        $this->addCmsFileToZone('cms-contact2', 'page1', 'right', 2);

        $this->addCmsFileToZone('cms-contact2', 'page2', 'center', 1);

        $this->addCmsFileToZone('cms-map-point1', 'pageMap', 'main', 1);
        $this->addCmsFileToZone('cms-map-point2', 'pageMap', 'main', 2);

        $this->addCmsFileToZone('cms-map-point3', 'pageCustomMap', 'map', 1);

        //--------------------------------------------------------
        // Azimut demo site

        $this->addCmsFileToZone('cms-product-kiosk-table', 'azimutHomePage', 'products');

        $this->addCmsFileToZone('cms-product-kiosk-table', 'azimutKiosksPage', 'products', 1);
        $this->addCmsFileToZone('cms-product-kiosk-easy-touch-up', 'azimutKiosksPage', 'products', 2);
        $this->addCmsFileToZone('cms-product-kiosk-arbre-communication', 'azimutKiosksPage', 'products', 3);
        $this->addCmsFileToZone('cms-product-kiosk-totem-plexy', 'azimutKiosksPage', 'products', 4);
        $this->addCmsFileToZone('cms-product-kiosk-museo', 'azimutKiosksPage', 'products', 5);
        $this->addCmsFileToZone('cms-product-kiosk-elite', 'azimutKiosksPage', 'products', 6);
        $this->addCmsFileToZone('cms-product-kiosk-design', 'azimutKiosksPage', 'products', 7);
        $this->addCmsFileToZone('cms-product-kiosk-totem', 'azimutKiosksPage', 'products', 8);
        $this->addCmsFileToZone('cms-product-kiosk-ventana', 'azimutKiosksPage', 'products', 9);
        $this->addCmsFileToZone('cms-product-kiosk-urbana', 'azimutKiosksPage', 'products', 10);
        $this->addCmsFileToZone('cms-product-kiosk-touchscreen', 'azimutKiosksPage', 'products', 11);
        $this->addCmsFileToZone('cms-product-kiosk-kiosque-universel', 'azimutKiosksPage', 'products', 12);

        $objectManager->flush();
    }

    private function addCmsFileToZone($cmsFileReference, $pageReference, $zoneName, $displayOrder = 1)
    {
        $zoneRepository = $this->objectManager->getRepository(Zone::class);

        $attachment = new ZoneCmsFileAttachment();
        $attachment
            ->setDisplayOrder($displayOrder)
            ->setZone($zoneRepository->findOneByNameAndPage(
                $this->getReference($pageReference),
                $zoneName
            ))
            ->setCmsFile($this->getReference($cmsFileReference))
        ;

        $this->objectManager->persist($attachment);

        return $attachment;
    }

    /**
    * {@inheritDoc}
    */
    public function getOrder()
    {
        return 20;
    }
}
