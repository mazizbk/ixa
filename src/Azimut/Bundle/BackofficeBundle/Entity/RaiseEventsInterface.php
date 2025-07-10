<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-05 16:45:18
 */

namespace Azimut\Bundle\BackofficeBundle\Entity;

use Symfony\Component\EventDispatcher\Event;

interface RaiseEventsInterface
{
    /**
     * Return all events raised and clear events collection
     *
     * @return Event[]
     */
    public function popEvents();
}
