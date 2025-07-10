<?php

namespace Azimut\Bundle\FrontofficeBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class CustomCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('azimut_fo.access_right')) {
            return;
        }

        $definition = $container->getDefinition(
            'azimut_fo.access_right'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'azimut_fo.rights'
        );

        foreach ($taggedServices as $id => $attributes) {
            $definition->addMethodCall(
                'addRights',
                array(new Reference($id))
            );
        }
    }
}
