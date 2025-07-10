<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@azimut.net>
 * date:   2015-09-24 14:43:11
 */

namespace Azimut\Bundle\HttpKernelExtraBundle\Listener;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class TransformRequestListener
{
    public function onKernelRequest(GetResponseEvent $event)
    {
        // don't do anything if it's not the master request
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();

        // restrict to json format
        if ('json' !== $request->getContentType()) {
            return;
        }

        if(mb_strlen($request->getContent())===0){
            return;
        }

        $requestData = json_decode($request->getContent(), true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            $event->setResponse(Response::create('Unable to decode JSON request.', Response::HTTP_BAD_REQUEST));
        }


        if (null !== $requestData) {
            // recursively remove empty values in arrays
            $request->request->replace($this->removeEmptyArrayValues($requestData));
        }
    }


    private function removeEmptyArrayValues($array)
    {
        foreach ($array as &$value) {
            if (is_array($value)) {
                $value = $this->removeEmptyArrayValues($value);
            }
        }

        return array_filter($array, function ($value) {
            return !(null === $value || '' === $value);
        });
    }
}
