<?php


namespace Azimut\Bundle\MontgolfiereAppBundle\Controller;


use JMS\Serializer\SerializationContext;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

trait BackofficeXHRController
{

    protected static function isXMLHTTPRequest(Request $request): bool
    {
        return $request->isXmlHttpRequest() || in_array('application/json', $request->getAcceptableContentTypes());
    }

    protected function serialize($data, array $serializationGroups = []): Response
    {
        $context = new SerializationContext();
        if(!empty($serializationGroups)) {
            $context->setGroups($serializationGroups);
        }
        $response = new JsonResponse();
        $response->setJson($this->serializer->serialize($data, 'json', $context));

        return $response;
    }
}
