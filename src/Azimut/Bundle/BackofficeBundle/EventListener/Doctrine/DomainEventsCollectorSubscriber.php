<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-05 16:55:58
 */

namespace Azimut\Bundle\BackofficeBundle\EventListener\Doctrine;

use Azimut\Bundle\BackofficeBundle\Entity\RaiseEventsInterface;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\Event;

class DomainEventsCollectorSubscriber implements EventSubscriber
{
    /**
     * @var Event[]
     */
    private $events = [];

    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    public function __construct(EventDispatcherInterface $eventDispatcher)
    {
        $this->eventDispatcher = $eventDispatcher;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postPersist,
            Events::postUpdate,
            Events::preRemove,
            Events::postRemove,
        ];
    }

    public function postPersist(LifecycleEventArgs $event)
    {
        $this->collectEvent($event);
    }

    public function postUpdate(LifecycleEventArgs $event)
    {
        $this->collectEvent($event);
    }

    /**
     * We listen to preRemove in case Doctrine extensions soft delete is used
     * (in this case postRemove is not called)
     */
    public function preRemove(LifecycleEventArgs $event)
    {
        $this->collectEvent($event);
    }

    public function postRemove(LifecycleEventArgs $event)
    {
        $this->collectEvent($event);
    }

    /**
     * Dispatch all collected events
     */
    public function dispatchCollectedEvents()
    {
        $events = $this->events;
        $this->events = [];

        foreach ($events as $event) {
            $this->eventDispatcher->dispatch($event::NAME, $event);
        }

        // Listener can raise new events, dispatch those
        if ($this->events) {
            $this->dispatchCollectedEvents();
        }
    }

    /**
     * Collect event
     */
    private function collectEvent(LifecycleEventArgs $event)
    {
        $entity = $event->getEntity();

        if (!$entity instanceof RaiseEventsInterface) {
            return;
        }

        foreach ($entity->popEvents() as $event) {
            $this->events[spl_object_hash($event)] = $event;
        }
    }
}
