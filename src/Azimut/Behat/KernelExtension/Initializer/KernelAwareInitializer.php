<?php

namespace Azimut\Behat\KernelExtension\Initializer;

use Azimut\Behat\KernelExtension\KernelAwareInterface;
use Azimut\Behat\KernelExtension\KernelFactory;
use Behat\Behat\Context\ContextInterface;
use Behat\Behat\Context\Initializer\InitializerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class KernelAwareInitializer implements InitializerInterface, EventSubscriberInterface
{
    private $kernelFactory;

    public function __construct(KernelFactory $kernelFactory)
    {
        $this->kernelFactory = $kernelFactory;
    }

    public function supports(ContextInterface $context)
    {
        return $context instanceof KernelAwareInterface;
    }

    /**
     * Initializes provided context.
     *
     * @param ContextInterface $context
     */
    public function initialize(ContextInterface $context)
    {
        $context->setKernelFactory($this->kernelFactory);
    }

    public static function getSubscribedEvents()
    {
        return array();
    }
}
