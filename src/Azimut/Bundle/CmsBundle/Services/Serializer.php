<?php
/**
 * Created by mikaelp on 2/13/2017 2:08 PM
 */

namespace Azimut\Bundle\CmsBundle\Services;

use FOS\RestBundle\Serializer\JMSSerializerAdapter;
use JMS\Serializer\SerializerInterface;
use JMS\Serializer\ContextFactory\SerializationContextFactoryInterface;
use JMS\Serializer\ContextFactory\DeserializationContextFactoryInterface;
use JMS\Serializer\JsonSerializationVisitor;
use Symfony\Component\HttpFoundation\RequestStack;
use FOS\RestBundle\Context\Context;

class Serializer extends JMSSerializerAdapter
{
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var JsonSerializationVisitor
     */
    private $jsonSerializationVisitor;

    public function __construct(
        SerializerInterface $serializer,
        JsonSerializationVisitor $jsonSerializationVisitor,
        RequestStack $requestStack,
        SerializationContextFactoryInterface $serializationContextFactory = null,
        DeserializationContextFactoryInterface $deserializationContextFactory = null
    ) {
        parent::__construct($serializer, $serializationContextFactory, $deserializationContextFactory);
        $this->requestStack = $requestStack;
        $this->jsonSerializationVisitor = $jsonSerializationVisitor;
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($data, $format, Context $context = null)
    {
        $noUnicode = $this->requestStack->getMasterRequest()->query->has('nounicode');
        if($noUnicode) {
            $originalOptions = $this->jsonSerializationVisitor->getOptions();
            if(!($originalOptions & JSON_UNESCAPED_UNICODE)) {
                $options = $originalOptions | JSON_UNESCAPED_UNICODE;
                $this->jsonSerializationVisitor->setOptions($options);
            }

            $data = parent::serialize($data, $format, $context);
            $this->jsonSerializationVisitor->setOptions($originalOptions);
        }
        else {
            $data = parent::serialize($data, $format, $context);
        }

        return $data;
    }
}
