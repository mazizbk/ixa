<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@gmail.com>
 * date:    2017-02-03 10:12:18
 */

namespace Azimut\Bundle\FrontofficeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Reference;

class SearchEngineProviderPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->has('azimut_frontoffice.search_engine_provider_chain')) {
            return;
        }
        $definition = $container->findDefinition('azimut_frontoffice.search_engine_provider_chain');

        $taggedServices = $container->findTaggedServiceIds('azimut_frontoffice.search_engine_provider');

        foreach ($taggedServices as $id => $tags) {
            $definition->addMethodCall('addProvider', array(new Reference($id)));
        }
    }
}
