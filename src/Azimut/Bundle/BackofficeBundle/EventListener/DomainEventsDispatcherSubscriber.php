<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2019-11-05 17:07:27
 */

namespace Azimut\Bundle\BackofficeBundle\EventListener;

use Symfony\Component\Console\ConsoleEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Azimut\Bundle\BackofficeBundle\EventListener\Doctrine\DomainEventsCollectorSubscriber;

class DomainEventsDispatcherSubscriber implements EventSubscriberInterface
{
    /**
     * @var DomainEventsCollectorSubscriber
     */
    private $domainEventsCollector;

    public function __construct(DomainEventsCollectorSubscriber $domainEventsCollector)
    {
        $this->domainEventsCollector = $domainEventsCollector;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::RESPONSE   => 'onKernelResponse',
            ConsoleEvents::TERMINATE => 'onConsoleTerminate',
        ];
    }

    public function onKernelResponse(FilterResponseEvent $responseEvent)
    {
        $this->domainEventsCollector->dispatchCollectedEvents();
    }

    public function onConsoleTerminate()
    {
        $this->domainEventsCollector->dispatchCollectedEvents();
    }
}
