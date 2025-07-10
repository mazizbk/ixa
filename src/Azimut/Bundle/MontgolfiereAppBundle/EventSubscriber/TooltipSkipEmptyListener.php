<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Tooltip;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\UnitOfWork;

class TooltipSkipEmptyListener implements EventSubscriber
{
    public function getSubscribedEvents()
    {
        return [
            Events::onFlush,
        ];
    }

    public function onFlush(OnFlushEventArgs $event): void
    {
        $em = $event->getEntityManager();
        $uow = $em->getUnitOfWork();
        foreach ($uow->getScheduledEntityInsertions() as $scheduledEntityInsertion) {
            $this->removeEmptyTooltip($scheduledEntityInsertion, $uow);
        }
        foreach ($uow->getScheduledEntityUpdates() as $scheduledEntityUpdate) {
            $this->removeEmptyTooltip($scheduledEntityUpdate, $uow);
        }
    }

    private function removeEmptyTooltip($entity, UnitOfWork $uow): void
    {
        if(!$entity instanceof Tooltip) {
            return;
        }

        if($entity->getText()) {
            return;
        }

        $uow->remove($entity);
    }

}
