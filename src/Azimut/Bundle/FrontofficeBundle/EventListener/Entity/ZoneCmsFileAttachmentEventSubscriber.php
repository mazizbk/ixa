<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-05 17:34:35
 */

namespace Azimut\Bundle\FrontofficeBundle\EventListener\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Azimut\Bundle\FrontofficeBundle\Event\Entity\ZoneCmsFileAttachmentRemoved;
use Azimut\Bundle\FrontofficeBundle\Entity\Zone;

class ZoneCmsFileAttachmentEventSubscriber implements EventSubscriberInterface
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            ZoneCmsFileAttachmentRemoved::NAME => 'onZoneCmsFileAttachmentRemoved',
        ];
    }

    // Be carefull that things done here are executed after the Doctrine's transaction commit, so the entity is already removed in the DB
    public function onZoneCmsFileAttachmentRemoved(ZoneCmsFileAttachmentRemoved $event)
    {
        $attachment = $event->getZoneCmsFileAttachment();
        $zone = $attachment->getZone();

        // If zone entity is not managed anymore, it has been deleted
        if (null == $zone->getId()) {
            return;
        }

        // Relative display order update (shift only greater display orders)
        $this->entityManager->getRepository(Zone::class)
            ->decreaseAttachmentsDisplayOrderStartingAt($zone, $attachment->getDisplayOrder())
        ;
    }
}
