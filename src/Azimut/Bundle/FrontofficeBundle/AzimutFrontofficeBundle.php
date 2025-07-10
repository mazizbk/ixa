<?php

/**
 * Bundle for handling routes and structure
 * This is part of the Azimut System software
 *
 * Gerda Le Duc <gerda.leduc@azimut.net>
 */

namespace Azimut\Bundle\FrontofficeBundle;

use Azimut\Bundle\FrontofficeBundle\DependencyInjection\Compiler\OverrideNelmioPass;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Azimut\Bundle\FrontofficeBundle\DependencyInjection\Compiler\SearchEngineProviderPass;

class AzimutFrontofficeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new OverrideNelmioPass(), PassConfig::TYPE_AFTER_REMOVING);
        $container->addCompilerPass(new SearchEngineProviderPass());
    }
}
