<?php
/**
 * Created by mikaelp on 2018-11-13 5:19 PM
 */

namespace Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\ClientContact;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class DeleteClientContactSubscriber implements EventSubscriber
{

    public function getSubscribedEvents()
    {
        return [
            Events::preRemove,
        ];
    }

    public function preRemove(LifecycleEventArgs $event)
    {
        $entity = $event->getObject();
        if(!$entity instanceof ClientContact) {
            return;
        }

        if(!$entity->getFrontUser()) {
            return;
        }

        $event->getObjectManager()->remove($entity->getFrontUser());
    }
}
