<?php

namespace Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber;

use Azimut\Bundle\MontgolfiereAppBundle\Entity\AnalysisVersion;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\Question;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class SetAnalysisVersionSubscriber implements EventSubscriber
{

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
        ];
    }

    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $entity = $eventArgs->getObject();
        if (!$entity instanceof Campaign && !$entity instanceof Question) {
            return;
        }

        $entity->setAnalysisVersion($eventArgs->getObjectManager()->getRepository(AnalysisVersion::class)->getLastVersion());
    }
}
