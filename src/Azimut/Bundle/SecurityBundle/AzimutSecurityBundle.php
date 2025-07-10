<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2013-08-12 16:27:38
 *
 * Bundle for handling access rights on user and groups
 * This is part of the Azimut System software
 */

namespace Azimut\Bundle\SecurityBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use Azimut\Bundle\SecurityBundle\DependencyInjection\Compiler\RolesCompilerPass;

class AzimutSecurityBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new RolesCompilerPass());
    }
}
