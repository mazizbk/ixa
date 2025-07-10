<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignAutomaticAffectation;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactor;
use Azimut\Bundle\MontgolfiereAppBundle\Entity\CampaignSortingFactorValue;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class PreventInvalidAutomaticAffectationSubscriber implements EventSubscriber
{

    public function getSubscribedEvents()
    {
        return [
            Events::postRemove,
            Events::postPersist,
            Events::postUpdate,
        ];
    }

    public function postRemove(LifecycleEventArgs $event): void
    {
        $this->handleEvent($event);
    }

    public function postPersist(LifecycleEventArgs $event): void
    {
        $this->handleEvent($event);
    }

    public function postUpdate(LifecycleEventArgs $event): void
    {
        $this->handleEvent($event);
    }

    public function handleEvent(LifecycleEventArgs $event): void
    {
        $entity = $event->getEntity();
        if($entity instanceof CampaignSortingFactor) {
            $this->fixCampaignAffectations($event->getEntityManager(), $entity->getCampaign());
        }
        elseif($entity instanceof CampaignSortingFactorValue) {
            $this->fixCampaignAffectations($event->getEntityManager(), $entity->getSortingFactor()->getCampaign());
        }
    }

    private function fixCampaignAffectations(EntityManagerInterface $entityManager, Campaign $campaign): void
    {
        $entityManager->refresh($campaign);
        $entityManager->getRepository(CampaignAutomaticAffectation::class)->deleteIncompleteAffectations($campaign);
    }
}
