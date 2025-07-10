<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-05 16:47:20
 *
 * This trait adds support for domain events on an entity
 *
 * Usage :
 *     use Azimut\Bundle\BackofficeBundle\Entity\RaiseEventsInterface;
 *     use Azimut\Bundle\BackofficeBundle\Entity\Traits\RaiseEventsTrait;
 *
 *     use Azimut\Bundle\MyBundle\Event\Entity\MyEntityCreated;
 *
 *     class MyEntity implements RaiseEventsInterface
 *     {
 *              use RaiseEventsTrait;
 *
 *              public function __construct()
 *              {
 *                  $this->id = uniqid();
 *                  $this->raiseEvent(new MyEntityCreated($this));
 *              }
 *     }
 *
 */

namespace Azimut\Bundle\BackofficeBundle\Entity\Traits;

use Symfony\Component\EventDispatcher\Event;

trait RaiseEventsTrait
{
    /**
     * @var array
     */
    protected $events = [];

    /**
     * {@inheritdoc}
     */
    public function popEvents()
    {
        $events = $this->events;

        $this->events = [];

        return $events;
    }

    /**
     * Raise an event
     *
     * @return self
     */
    protected function raiseEvent(Event $event)
    {
        $this->events[] = $event;

        return $this;
    }
}
