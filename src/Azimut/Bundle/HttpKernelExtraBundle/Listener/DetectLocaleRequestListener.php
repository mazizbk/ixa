<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-05-18 14:50:56
 */

namespace Azimut\Bundle\HttpKernelExtraBundle\Listener;

use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class DetectLocaleRequestListener
{
    /**
     * @var array
     */
    private $availableLocales;

    /**
     * @var string
     */
    private $defaultLocale;

    public function __construct(array $availableLocales, $defaultLocale)
    {
        $this->availableLocales = $availableLocales;
        $this->defaultLocale = $defaultLocale;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        // Ignore if not the main request
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        // Check if locale is supported
        if (!in_array($request->getLocale(), $this->availableLocales)) {
            // Use default locale
            $request->setLocale($this->defaultLocale);
        }
    }
}
