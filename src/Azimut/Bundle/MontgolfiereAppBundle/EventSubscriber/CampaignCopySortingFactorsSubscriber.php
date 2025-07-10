<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\EventSubscriber;


use Azimut\Bundle\MontgolfiereAppBundle\Entity\Campaign;
use Doctrine\Common\EventSubscriber;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;

class CampaignCopySortingFactorsSubscriber implements EventSubscriber
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
        if (!$entity instanceof Campaign) {
            return;
        }

        $campaignRepo = $eventArgs->getObjectManager()->getRepository(Campaign::class);

        // Find client last campaign
        $previousCampaign = $campaignRepo->createQueryBuilder('c')
            ->where('c.client = :client')
            ->setParameter(':client', $entity->getClient())
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;

        if(!$previousCampaign) {
            return;
        }

        $sortingFactors = $previousCampaign->getSortingFactors();
        foreach ($sortingFactors as $sortingFactor) {
            $entity->addSortingFactor(clone $sortingFactor);
        }
    }
}
