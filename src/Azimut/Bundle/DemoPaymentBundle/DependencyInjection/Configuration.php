<?php
/**
 * @author: Yoann Le Crom <yoann.lecrom@abstractive.fr>
 * date:    2018-12-13 11:16:27
 */

namespace Azimut\Bundle\DemoPaymentBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $builder = new TreeBuilder();

        $builder->root('azimut_demo_payment')
            ->children()
                ->scalarNode('normal_return_url')->end()
                ->scalarNode('cancel_return_url')->end()
                ->scalarNode('automatic_response_url')->end()
            ->end()
        ;

        return $builder;
    }
}
