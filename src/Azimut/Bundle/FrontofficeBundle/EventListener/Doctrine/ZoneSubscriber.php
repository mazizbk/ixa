<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-07-12 15:18:40
 */

namespace Azimut\Bundle\FrontofficeBundle\EventListener\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Events;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;

class ZoneSubscriber implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
        ];
    }

    public function preRemove(LifecycleEventArgs $eventArgs)
    {
        $em = $eventArgs->getEntityManager();
        $entity = $eventArgs->getEntity();

        if ($entity instanceof Zone) {
            // Delete attached CmsFile if Zone contain full CmsFile
            if ($entity->isFullZoneCmsFile()) {
                $em->remove($entity->getAttachments()[0]->getCmsFile());
            }
        }
    }
}
