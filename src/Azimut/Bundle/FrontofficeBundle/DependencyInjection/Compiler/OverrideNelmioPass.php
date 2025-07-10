<?php

namespace Azimut\Bundle\FrontofficeBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class OverrideNelmioPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('nelmio_api_doc.parser.form_type_parser')) {
            return;
        }

        $container
            ->getDefinition('nelmio_api_doc.parser.form_type_parser')
            ->setClass('Azimut\Bundle\FrontofficeBundle\Nelmio\FormTypeParser')
        ;
    }
}
