<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-01 12:10:13
 */

namespace Azimut\Bundle\FrontofficeBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Azimut\Bundle\FrontofficeBundle\Entity\ZoneDefinition;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;
use Azimut\Bundle\FrontofficeBundle\Entity\PageContent;
use Doctrine\ORM\UnitOfWork;

class ZoneDefinitionSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $uow = $em->getUnitOfWork();

        foreach ($uow->getScheduledEntityInsertions() as $entity) {
            if ($entity instanceof ZoneDefinition) {
                $this->createPageContentsZones($entity, $em, $uow);
            }
        }
    }

    private function createPageContentsZones(ZoneDefinition $zoneDefinition, EntityManagerInterface $em, UnitOfWork $uow)
    {
        /** @var PageContent[] $pages */
        $pages = $em->getRepository(PageContent::class)->findBy(['layout' => $zoneDefinition->getLayout()]);

        foreach ($pages as $page) {
            $zone = new Zone();
            $zone->setZoneDefinition($zoneDefinition);
            $page->addZone($zone);

            $uow->computeChangeSet($em->getMetadataFactory()->getMetadataFor(PageContent::class), $page);
        }
    }
}
