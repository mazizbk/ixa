<?php
/**
 * @author: Gerda Le Duc <gerda.leduc@azimut.net>
 * date:   2014-01-08 15:23:56
 */

namespace Azimut\Bundle\SecurityBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class RolesCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('azimut_security.role_provider_chain')) {
            return;
        }

        $definition = $container->getDefinition(
            'azimut_security.role_provider_chain'
        );

        $taggedServices = $container->findTaggedServiceIds(
            'role_provider'
        );

        foreach ($taggedServices as $id => $tagAttributes) {
            foreach ($tagAttributes as $attributes) {
                $definition->addMethodCall(
                    'addProvider', array(new Reference($id), $attributes["alias"])
                );
            }
        }
    }
}
