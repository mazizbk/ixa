<?php

namespace Azimut\Behat\KernelExtension;

interface KernelAwareInterface
{
    public function setKernelFactory(KernelFactory $kernelFactory);
}
